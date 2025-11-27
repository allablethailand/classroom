# actions/python/generate_embedding.py

import cv2
import insightface
import numpy as np
import os
import json
import sys
import traceback 
import logging

# ตั้งค่า Logging 
logging.basicConfig(level=logging.ERROR, filename='face_embedding_error.log', 
                    format='%(asctime)s - %(levelname)s - %(message)s')

def generate_embedding(data):
    """
    รับ Path รูปภาพและ ID มาสร้าง Face Embedding
    :param data: dict ที่มี file_path (str), file_id (int)
    :return: dict ผลลัพธ์
    """
    
    file_path = data.get('file_path', None)
    file_id = data.get('file_id', 'N/A')
    
    if not file_path:
        return {"status": "error", "message": "ไม่พบ Path รูปภาพ"}

    try:
        # ใช้โมเดลเดียวกันกับที่ใช้ใน myphoto.py เพื่อให้ Embedding เข้ากันได้
        app = insightface.app.FaceAnalysis(name='buffalo_l')
        # ใช้ ctx_id=0 สำหรับ GPU, ctx_id=-1 สำหรับ CPU
        app.prepare(ctx_id=-1, det_size=(640, 640)) 
        
        normalized_path = os.path.normpath(file_path) 
        
        if not os.path.exists(normalized_path):
            logging.error(f"File Not Found: {normalized_path} (File ID: {file_id})")
            return {"status": "error", "message": f"ไม่พบไฟล์: {os.path.basename(file_path)}"}

        img = cv2.imread(normalized_path, cv2.IMREAD_COLOR) 
        if img is None:
            logging.error(f"Failed to load Image: {normalized_path} (File ID: {file_id})")
            return {"status": "error", "message": "ไม่สามารถโหลดรูปภาพได้"}

        faces = app.get(img)
        
        if len(faces) == 0:
            return {"status": "warning", "message": "ไม่พบใบหน้า", "embedding": None}

        # ใช้ใบหน้าแรกที่พบ (ปกติรูปโปรไฟล์จะมีใบหน้าเดียว)
        face = faces[0]
        # คำนวณ Embedding และ Normalize
        embedding = face.embedding / np.linalg.norm(face.embedding) 
        
        # แปลง NumPy array เป็น List ของ Float เพื่อให้ JSON.dumps แปลงได้
        embedding_list = embedding.tolist() 

        return {
            "status": "success",
            "message": "สร้าง Face Embedding สำเร็จ",
            "embedding": embedding_list
        }

    except Exception as e:
        error_msg = f"File ID {file_id} - Path {file_path} - Error: {str(e)}\n{traceback.format_exc()}"
        logging.error(error_msg)
        return {
            "status": "error", 
            "message": "เกิดข้อผิดพลาดในการประมวลผล (โปรดตรวจสอบ Server Log)" 
        }

if __name__ == "__main__":
    if len(sys.argv) < 2:
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

    result = generate_embedding(input_data)
    # แสดงผลลัพธ์สุดท้ายเป็น JSON ให้ PHP อ่าน
    print(json.dumps(result))