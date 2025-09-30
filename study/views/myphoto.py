import cv2
import insightface
import numpy as np
import os
import json
import sys

def process_face_recognition(data):
    """
    รับข้อมูลจาก PHP และทำการรู้จำใบหน้า
    :param data: dict ที่มี ref_paths, group_paths, output_dir, student_id
    :return: dict ผลลัพธ์
    """
    try:
        # โหลด InsightFace (ต้องแน่ใจว่ารันได้ในสภาพแวดล้อมจริง)
        app = insightface.app.FaceAnalysis(name='buffalo_l')
        # ตรวจสอบการตั้งค่า ctx_id ให้เหมาะสมกับสภาพแวดล้อม (0 คือ GPU, -1 คือ CPU)
        app.prepare(ctx_id=-1, det_size=(640, 640)) 

        ref_paths = data['ref_paths']
        group_paths = data['group_paths']
        output_dir = data['output_dir'] # โฟลเดอร์สำหรับผลลัพธ์
        student_id = data['student_id']
        threshold = 0.45 # Threshold (สามารถปรับได้)

        # ----------------------------------------------------
        # 1. โหลด reference และสร้าง embedding
        # ----------------------------------------------------
        refs = []
        for i, path in enumerate(ref_paths):
            # ตรวจสอบว่าไฟล์มีอยู่จริง (ในสภาพแวดล้อม Production อาจเป็น URL ที่ต้องดาวน์โหลด)
            if not os.path.exists(path):
                # ถ้าเป็น URL อาจต้องใช้ requests.get(path) เพื่อดาวน์โหลดก่อน
                continue 

            img = cv2.imread(path)
            if img is None:
                continue

            faces = app.get(img)
            if len(faces) == 0:
                continue

            face = faces[0]
            embedding = face.embedding / np.linalg.norm(face.embedding)
            # ชื่ออ้างอิงใช้ student_id + index
            name = f"{student_id}_{i}" 
            refs.append({
                "name": name,
                "embedding": embedding,
                "original_path": path
            })

        if len(refs) == 0:
            return {"status": "success", "found_images": []} # ไม่พบ ref face

        # ----------------------------------------------------
        # 2. ตรวจสอบในรูปกลุ่ม
        # ----------------------------------------------------
        found_group_images = set() # ใช้ set เพื่อเก็บเฉพาะ path รูปกลุ่มที่ไม่ซ้ำ

        for group_path in group_paths:
            if not os.path.exists(group_path):
                continue

            group_img = cv2.imread(group_path)
            if group_img is None:
                continue
            
            # ตรวจจับใบหน้าทั้งหมดในรูปกลุ่ม
            faces_group = app.get(group_img)
            
            is_student_found_in_group = False

            for face in faces_group:
                emb = face.embedding / np.linalg.norm(face.embedding)

                best_sim = -1
                for ref in refs:
                    sim = np.dot(ref["embedding"], emb)
                    if sim > best_sim:
                        best_sim = sim
                
                # ถ้า similarity สูงกว่า threshold แสดงว่าพบใบหน้าของนักเรียนคนนี้
                if best_sim > threshold:
                    is_student_found_in_group = True
                    break # พบแล้วในรูปนี้ ไปรูปกลุ่มถัดไป

            if is_student_found_in_group:
                # บันทึก path ของรูปกลุ่มที่พบ
                found_group_images.add(group_path)


        return {
            "status": "success",
            "found_images": list(found_group_images)
        }

    except Exception as e:
        # หากเกิดข้อผิดพลาดในการประมวลผล
        return {"status": "error", "message": str(e), "traceback": sys.exc_info()}

if __name__ == "__main__":
    # รับ argument จาก command line (PHP ส่งมา)
    if len(sys.argv) < 2:
        print(json.dumps({"status": "error", "message": "No JSON argument provided"}))
        sys.exit(1)

    try:
        # อ่าน JSON string จาก argument
        input_data = json.loads(sys.argv[1])
    except json.JSONDecodeError:
        print(json.dumps({"status": "error", "message": "Invalid JSON input"}))
        sys.exit(1)

    # รันฟังก์ชันประมวลผล
    result = process_face_recognition(input_data)
    
    # พิมพ์ผลลัพธ์ออกทาง standard output เพื่อให้ PHP shell_exec อ่านได้
    print(json.dumps(result))