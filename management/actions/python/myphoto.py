import cv2
import insightface
import numpy as np
import os
import json
import sys
import traceback 

def process_face_recognition(data):
    """
    รับข้อมูลจาก PHP และทำการรู้จำใบหน้าแบบ Batch
    :param data: dict ที่มี all_students_ref_paths (dict), group_path (str), group_photo_id (int)
    :return: dict ผลลัพธ์
    """
    
    # 💡 ใช้ dict เพื่อเก็บ embeddings ของนักเรียนทุกคน: student_id => [embedding1, embedding2, ...]
    student_embeddings = {} 
    
    debug_info = {
        "group_path": data.get('group_path', 'N/A'),
        "group_photo_id": data.get('group_photo_id', 'N/A'),
        "total_students_processed": 0,
        "total_embeddings_created": 0,
        "load_ref_error": None
    }
    
    try:
        app = insightface.app.FaceAnalysis(name='buffalo_l')
        app.prepare(ctx_id=-1, det_size=(640, 640)) 

        all_students_ref_paths = data.get('all_students_ref_paths', {})
        group_path = data.get('group_path', None)
        threshold = 0.45 
        found_student_ids = [] # IDs ของนักเรียนที่พบในรูปกลุ่มนี้
        
        # ----------------------------------------------------
        # 1. โหลด reference ของนักเรียนทุกคนและสร้าง embedding
        # ----------------------------------------------------
        
        for student_id_str, ref_paths in all_students_ref_paths.items():
            student_id = int(student_id_str)
            student_embeddings[student_id] = []
            debug_info["total_students_processed"] += 1

            for path in ref_paths:
                if not os.path.exists(path):
                    continue 

                img = cv2.imread(path, cv2.IMREAD_COLOR) 
                if img is None:
                    continue

                faces = app.get(img)
                
                if len(faces) == 0:
                    continue

                # ใช้ใบหน้าแรกที่พบในรูปโปรไฟล์
                face = faces[0]
                embedding = face.embedding / np.linalg.norm(face.embedding) 
                student_embeddings[student_id].append(embedding)
                debug_info["total_embeddings_created"] += 1
        
        if debug_info["total_embeddings_created"] == 0:
             return {
                "status": "error", 
                "message": "ไม่พบใบหน้าใดๆ ในรูปโปรไฟล์ของนักเรียนทุกคน", 
                "debug": debug_info
            } 

        # ----------------------------------------------------
        # 2. ตรวจสอบในรูปกลุ่มเดียวที่เพิ่งอัปโหลด
        # ----------------------------------------------------
        
        if not group_path or not os.path.exists(group_path):
            return {
                "status": "error", 
                "message": "ไม่พบไฟล์รูปภาพกลุ่มที่ต้องการตรวจจับ", 
                "debug": debug_info
            }
            
        group_img = cv2.imread(group_path, cv2.IMREAD_COLOR)
        if group_img is None:
            return {
                "status": "error", 
                "message": "ไม่สามารถโหลดรูปภาพกลุ่มได้ (Corrupt or Invalid Format)", 
                "debug": debug_info
            }
        
        faces_group = app.get(group_img)
        
        if len(faces_group) == 0:
            return {
                "status": "success", 
                "found_student_ids": [],
                "message": "ไม่พบใบหน้าใดๆ ในรูปกลุ่ม", 
                "debug": debug_info
            }

        # วนลูปผ่านใบหน้าทั้งหมดในรูปกลุ่ม
        for face_in_group in faces_group:
            emb_group = face_in_group.embedding / np.linalg.norm(face_in_group.embedding)
            
            # วนลูปผ่านนักเรียนทุกคนเพื่อเทียบ similarity
            for student_id, ref_embeddings in student_embeddings.items():
                if student_id in found_student_ids:
                    continue # นักเรียนคนนี้ถูกพบแล้วในใบหน้าก่อนหน้า (ถ้ามีหลายใบหน้าในรูปโปรไฟล์)

                best_sim = -1
                # เปรียบเทียบกับรูปโปรไฟล์ทั้งหมดของนักเรียนคนนั้น (สูงสุด 5 รูป)
                for ref_emb in ref_embeddings:
                    sim = np.dot(ref_emb, emb_group)
                    if sim > best_sim:
                        best_sim = sim
                
                if best_sim > threshold:
                    found_student_ids.append(student_id)
                    # เมื่อพบแล้ว ให้ข้ามการเปรียบเทียบกับใบหน้าอื่นๆ ในรูปกลุ่มสำหรับนักเรียนคนนี้
                    # (เพราะเราสนใจแค่ว่า "มี" หรือ "ไม่มี" ในรูปกลุ่มนี้)
                    # แต่ถ้าอยากให้ทุกใบหน้าในรูปกลุ่มถูกเช็คกับทุกคน ให้ลบ break ออก
                    break 

        return {
            "status": "success",
            "found_student_ids": found_student_ids, # ส่งกลับแค่ ID นักเรียน
            "message": f"ตรวจจับสำเร็จ: พบ {len(found_student_ids)} คน",
            "debug": debug_info
        }

    except Exception as e:
        debug_info["load_ref_error"] = str(e) 
        return {
            "status": "error", 
            "message": f"Python Error: {str(e)}", 
            "traceback": traceback.format_exc(),
            "debug": debug_info
        }

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"status": "error", "message": "No JSON argument provided", "raw_input": sys.argv}))
        sys.exit(1)

    try:
        input_data = json.loads(sys.argv[1])
    except json.JSONDecodeError as e:
        print(json.dumps({
            "status": "error", 
            "message": f"Invalid JSON input: {str(e)}", 
            "raw_input": sys.argv[1]
        }))
        sys.exit(1)

    result = process_face_recognition(input_data)
    print(json.dumps(result))