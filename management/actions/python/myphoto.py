import cv2
import insightface
import numpy as np
import os
import json
import sys
import traceback 
import logging

# ตั้งค่า Logging (แทนการใช้ print เพื่อป้องกันข้อมูลรั่วไหล)
# **ไม่ต้องเปลี่ยนโค้ดนี้** เพราะ PHP ได้ดาวน์โหลดไฟล์มาที่ Local Temp Path แล้ว
logging.basicConfig(level=logging.ERROR, filename='face_detection_error.log', 
                    format='%(asctime)s - %(levelname)s - %(message)s')

def process_face_recognition(data):
    """
    รับข้อมูลจาก PHP และทำการรู้จำใบหน้าแบบ Batch
    :param data: dict ที่มี all_students_ref_paths (dict), group_path (str), group_photo_id (int)
    :return: dict ผลลัพธ์
    """
    
    student_embeddings = {} 
    
    try:
        app = insightface.app.FaceAnalysis(name='buffalo_l')
        # ใช้ ctx_id=0 สำหรับ GPU, ctx_id=-1 สำหรับ CPU
        app.prepare(ctx_id=-1, det_size=(640, 640)) 

        all_students_ref_paths = data.get('all_students_ref_paths', {})
        group_path = data.get('group_path', None)
        threshold = 0.45 
        found_student_ids = [] 
        
        # ----------------------------------------------------
        # 1. โหลด reference ของนักเรียนทุกคนและสร้าง embedding
        #    (Path ที่ได้มาคือ Local Temp Path แล้ว)
        # ----------------------------------------------------
        total_embeddings = 0
        for student_id_str, ref_paths in all_students_ref_paths.items():
            student_id = int(student_id_str)
            student_embeddings[student_id] = []

            for path in ref_paths:
                # Path เป็น Local Temp Path ที่ดาวน์โหลดจาก PHP มาแล้ว
                normalized_path = os.path.normpath(path) 
                
                if not os.path.exists(normalized_path):
                    # จะไม่เกิดขึ้นถ้า PHP ดาวน์โหลดสำเร็จ
                    logging.error(f"Ref File Not Found: {normalized_path}")
                    continue 

                # ✅ cv2.imread() สามารถอ่านไฟล์จาก Local Path ได้
                img = cv2.imread(normalized_path, cv2.IMREAD_COLOR) 
                if img is None:
                    logging.error(f"Failed to load Ref Image: {normalized_path}")
                    continue

                faces = app.get(img)
                if len(faces) == 0:
                    continue

                face = faces[0]
                embedding = face.embedding / np.linalg.norm(face.embedding) 
                student_embeddings[student_id].append(embedding)
                total_embeddings += 1
        
        if total_embeddings == 0:
             return {
                 "status": "error", 
                 "message": "ไม่พบใบหน้าใดๆ ในรูปโปรไฟล์อ้างอิงของนักเรียน"
             } 

        # ----------------------------------------------------
        # 2. ตรวจสอบในรูปกลุ่มเดียวที่เพิ่งอัปโหลด
        #    (Path ที่ได้มาคือ Local Temp Path แล้ว)
        # ----------------------------------------------------
        
        normalized_group_path = os.path.normpath(group_path)
        
        if not group_path or not os.path.exists(normalized_group_path):
            return {
                "status": "error", 
                "message": "ไม่พบไฟล์รูปภาพกลุ่มที่ต้องการตรวจจับ (Local Temp Path ผิดพลาด)"
            }
            
        group_img = cv2.imread(normalized_group_path, cv2.IMREAD_COLOR)
        if group_img is None:
            return {
                "status": "error", 
                "message": "ไม่สามารถโหลดรูปภาพกลุ่มได้ (Corrupt or Invalid Format)"
            }
        
        faces_group = app.get(group_img)
        
        if len(faces_group) == 0:
            return {
                "status": "success", 
                "found_student_ids": [],
                "message": "ไม่พบใบหน้าใดๆ ในรูปกลุ่ม"
            }

        # [ส่วน Logic Face Recognition เหมือนเดิม] ...
        for face_in_group in faces_group:
            emb_group = face_in_group.embedding / np.linalg.norm(face_in_group.embedding)
            
            for student_id, ref_embeddings in student_embeddings.items():
                if student_id in found_student_ids:
                    continue 

                best_sim = -1
                for ref_emb in ref_embeddings:
                    sim = np.dot(ref_emb, emb_group)
                    if sim > best_sim:
                        best_sim = sim
                
                if best_sim > threshold:
                    found_student_ids.append(student_id)
                    break 

        return {
            "status": "success",
            "found_student_ids": found_student_ids, 
            "message": f"ตรวจจับสำเร็จ: พบ {len(found_student_ids)} คน"
        }

    except Exception as e:
        # NEW: บันทึก Error เต็มรูปแบบลงใน Log File แทนการส่งกลับไปยังหน้าเว็บ
        error_msg = f"Group ID {data.get('group_photo_id', 'N/A')} - Path {data.get('group_path', 'N/A')} - Error: {str(e)}\n{traceback.format_exc()}"
        logging.error(error_msg)
        
        return {
            "status": "error", 
            "message": "เกิดข้อผิดพลาดในการประมวลผล (โปรดตรวจสอบ Server Log)" 
        }

if __name__ == "__main__":
    if len(sys.argv) < 2:
        # NEW: ไม่เผย Path ของ Server ในข้อความ error
        print(json.dumps({"status": "error", "message": "No JSON argument provided"}))
        sys.exit(1)

    try:
        input_data = json.loads(sys.argv[1])
    except json.JSONDecodeError as e:
        print(json.dumps({
            "status": "error", 
            "message": "Invalid JSON input"
        }))
        sys.exit(1)

    result = process_face_recognition(input_data)
    print(json.dumps(result))