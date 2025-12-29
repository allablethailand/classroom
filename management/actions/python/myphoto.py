import cv2
import insightface
import numpy as np
import os
import json
import sys
import traceback
import logging

# UTF-8 output
try:
    sys.stdout.reconfigure(encoding='utf-8')
    sys.stderr.reconfigure(encoding='utf-8')
except:
    pass

logging.basicConfig(
    level=logging.ERROR,
    filename='face_detection_error.log',
    format='%(asctime)s - %(levelname)s - %(message)s'
)

# -----------------------------------------------------------
# 🚀 โหลดโมเดล InsightFace ไว้ครั้งเดียว
# -----------------------------------------------------------
print("DEBUG: Loading InsightFace model...", file=sys.stderr)
FACE_APP = insightface.app.FaceAnalysis(name='buffalo_l')
FACE_APP.prepare(ctx_id=-1, det_size=(640, 640))
print("DEBUG: InsightFace loaded", file=sys.stderr)


def extract_group_faces(group_path):
    """
    🟢 STEP 1: Extract embeddings จากรูปกลุ่ม (ทำครั้งเดียว)
    Returns: list of {face_index, embedding, bbox}
    """
    try:
        print(f"DEBUG: Loading group image: {group_path}", file=sys.stderr)
        
        if not os.path.exists(group_path):
            return {"status": "error", "message": f"ไฟล์ไม่พบ: {group_path}"}
        
        img = cv2.imread(group_path)
        if img is None:
            try:
                with open(group_path, 'rb') as f:
                    img_data = f.read()
                img = cv2.imdecode(np.frombuffer(img_data, np.uint8), cv2.IMREAD_COLOR)
            except:
                pass
        
        if img is None:
            return {"status": "error", "message": f"ไม่สามารถโหลดรูปได้: {group_path}"}
        
        # ⚡ Detect faces
        faces = FACE_APP.get(img)
        print(f"DEBUG: Found {len(faces)} faces", file=sys.stderr)
        
        if len(faces) == 0:
            return {
                "status": "success",
                "faces": [],
                "message": "ไม่พบใบหน้าในรูป"
            }
        
        # ⚡ Normalize embeddings ทีเดียว
        face_data = []
        for idx, face in enumerate(faces):
            if face.embedding is None:
                continue
            
            emb = face.embedding.astype(np.float32)
            norm = np.linalg.norm(emb)
            if norm == 0:
                continue
            
            emb_normalized = (emb / norm).tolist()
            
            # bbox format: [x, y, w, h]
            bbox = face.bbox.tolist() if hasattr(face, 'bbox') else []
            
            face_data.append({
                "face_index": idx,
                "embedding": emb_normalized,
                "bbox": bbox
            })
        
        return {
            "status": "success",
            "faces": face_data,
            "message": f"Extract สำเร็จ {len(face_data)} ใบหน้า"
        }
        
    except Exception as e:
        err = traceback.format_exc()
        logging.error(err)
        print(f"DEBUG ERROR: {err}", file=sys.stderr)
        return {"status": "error", "message": str(e)}


def match_faces_vectorized(group_faces_data, student_ref_embeddings, threshold=0.2):
    """
    🟢 STEP 2: Match faces แบบ Ultra Fast (ใช้ NumPy vectorization เต็มรูปแบบ)
    """
    try:
        # Build reference matrix
        id_list = []
        emb_list = []
        
        for sid, ref_list in student_ref_embeddings.items():
            try:
                sid_int = int(sid)
            except:
                continue
            
            for emb in ref_list:
                if isinstance(emb, list) and len(emb) == 512:
                    v = np.array(emb, dtype=np.float32)
                    id_list.append(sid_int)
                    emb_list.append(v)
        
        if not emb_list:
            return {"status": "error", "message": "ไม่พบ reference embeddings"}
        
        ref_matrix = np.vstack(emb_list)  # shape: (N_ref, 512)
        ref_ids = np.array(id_list)       # shape: (N_ref,)
        
        print(f"DEBUG: Ref matrix shape: {ref_matrix.shape}", file=sys.stderr)
        
        # Build group matrix
        group_emb_list = []
        face_indices = []
        
        for face in group_faces_data:
            emb = face.get("embedding")
            if emb and len(emb) == 512:
                group_emb_list.append(np.array(emb, dtype=np.float32))
                face_indices.append(face.get("face_index"))
        
        if not group_emb_list:
            return {
                "status": "success",
                "found_student_ids": [],
                "match_details": [],
                "message": "ไม่มีใบหน้าที่สามารถ match ได้"
            }
        
        group_matrix = np.vstack(group_emb_list)  # shape: (N_group, 512)
        
        print(f"DEBUG: Group matrix shape: {group_matrix.shape}", file=sys.stderr)
        
        # ⚡⚡⚡ ULTRA FAST: Matrix multiplication ทีเดียว
        # similarity_matrix shape: (N_group, N_ref)
        similarity_matrix = group_matrix @ ref_matrix.T
        
        # หา best match สำหรับแต่ละใบหน้า
        best_indices = np.argmax(similarity_matrix, axis=1)  # shape: (N_group,)
        best_sims = similarity_matrix[np.arange(len(best_indices)), best_indices]
        best_ids = ref_ids[best_indices]
        
        # สร้าง match details
        match_details = []
        found_ids = []
        
        for i, (face_idx, sim, student_id) in enumerate(zip(face_indices, best_sims, best_ids)):
            sim_float = float(sim)
            student_id_int = int(student_id)
            matched = sim_float > threshold
            
            match_details.append({
                "face_index": face_idx,
                "best_student_id": student_id_int,
                "best_similarity": sim_float,
                "matched": matched
            })
            
            if matched and student_id_int not in found_ids:
                found_ids.append(student_id_int)
            
            print(f"DEBUG FACE {face_idx}: ID={student_id_int}, sim={sim_float:.4f}", 
                  file=sys.stderr)
        
        return {
            "status": "success",
            "found_student_ids": found_ids,
            "match_details": match_details,
            "message": f"พบ {len(found_ids)} คน จาก {len(face_indices)} ใบหน้า"
        }
        
    except Exception as e:
        err = traceback.format_exc()
        logging.error(err)
        print(f"DEBUG ERROR: {err}", file=sys.stderr)
        return {"status": "error", "message": str(e)}


def process_face_recognition(data):
    """
    Main processing function
    mode: 'extract' หรือ 'match'
    """
    try:
        mode = data.get('mode', 'full')
        
        if mode == 'extract':
            # 🟢 MODE 1: Extract embeddings จากรูปกลุ่ม (ทำครั้งเดียว)
            group_path = data.get('group_path')
            return extract_group_faces(group_path)
        
        elif mode == 'match':
            # 🟢 MODE 2: Match faces (ใช้ embeddings ที่ extract ไว้แล้ว)
            group_faces = data.get('group_faces', [])
            student_embeddings = data.get('all_students_ref_embeddings', {})
            threshold = data.get('threshold', 0.2)
            
            return match_faces_vectorized(group_faces, student_embeddings, threshold)
        
        else:
            # 🟡 MODE 3: Full process (backward compatibility)
            print("DEBUG: Using FULL mode (slower)", file=sys.stderr)
            
            all_students_ref_embeddings = data.get('all_students_ref_embeddings', {})
            group_path = data.get('group_path')
            threshold = data.get('threshold', 0.2)
            
            # Extract
            extract_result = extract_group_faces(group_path)
            if extract_result['status'] != 'success':
                return extract_result
            
            # Match
            return match_faces_vectorized(
                extract_result['faces'],
                all_students_ref_embeddings,
                threshold
            )
    
    except Exception as e:
        err = traceback.format_exc()
        logging.error(err)
        print(f"DEBUG ERROR: {err}", file=sys.stderr)
        return {"status": "error", "message": str(e)}


# -----------------------------------------------------------
# Main
# -----------------------------------------------------------
if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"status": "error", "message": "No JSON file"}))
        sys.exit(1)
    
    json_file = sys.argv[1]
    try:
        with open(json_file, "r", encoding="utf-8") as f:
            data = json.load(f)
    except Exception as e:
        print(json.dumps({"status": "error", "message": str(e)}))
        sys.exit(1)
    
    result = process_face_recognition(data)
    print(json.dumps(result, ensure_ascii=False))