<?php
	header('Content-Type: text/html; charset=UTF-8');
	session_start();
    $base_include = $_SERVER['DOCUMENT_ROOT'];
    $base_path = '';
    if($_SERVER['HTTP_HOST'] == 'localhost'){
       $request_uri = $_SERVER['REQUEST_URI'];
       $exl_path = explode('/',$request_uri);
       if(!file_exists($base_include."/dashboard.php")){
           $base_path .= "/".$exl_path[1];
       }
       $base_include .= "/".$exl_path[1];
    }
    DEFINE('base_path', $base_path);
    DEFINE('base_include', $base_include);
	require_once($base_include."/lib/connect_sqli.php");
    if (!isset($_SESSION['emp_id'])) {
        echo json_encode([
            'status' => 'redirect',
            'message' => 'Session expired',
            'redirect_url' => '/index.php'
        ]);
        exit;
    }
    $fsData = getBucketMaster();
    $filesystem_user = $fsData['fs_access_user'];
    $filesystem_pass = $fsData['fs_access_pass'];
    $filesystem_host = $fsData['fs_host'];
    $filesystem_path = $fsData['fs_access_path'];
    $filesystem_type = $fsData['fs_type'];
    $fs_id = $fsData['fs_id'];
	setBucket($fsData);

    // ============ ฟังก์ชันสร้าง Default Albums (เพิ่ม Duplicates) ============
    function ensureDefaultAlbums($classroom_id, $comp_id, $emp_id) {
    $default_albums = [
        'Public' => 'Images visible to everyone (auto-filtered by status)',
        'Report' => 'Images with reports from users (auto-filtered by status)',
        'Delete' => 'Deleted images (auto-filtered by status)',
        'Restrict' => 'Private images not visible to users (auto-filtered by status)',
        'Duplicates' => 'Suspected duplicate photo groups based on face recognition (auto-filtered)'
    ];
    
    foreach ($default_albums as $album_name => $album_desc) {
        $existing = select_data(
            "classroom_id",
            "classroom_album",
            "where classroom_id = '{$classroom_id}' and album_name = '{$album_name}' and status = 0"
        );
        
        if (!$existing || count($existing) == 0) {
            insert_data(
                "classroom_album",
                "(classroom_id, comp_id, album_name, album_description, status, emp_create, date_create, emp_modify, date_modify)",
                "('{$classroom_id}', '{$comp_id}', '{$album_name}', '{$album_desc}', 0, '{$emp_id}', NOW(), '{$emp_id}', NOW())"
            );
        }
    }
}

function getDefaultAlbumId($classroom_id, $album_name) {
    $album = select_data(
        "classroom_id",
        "classroom_album",
        "where classroom_id = '{$classroom_id}' and album_name = '{$album_name}' and status = 0"
    );
    
    if ($album && count($album) > 0) {
        return $album[0]['classroom_id'];
    }
    return null;
}

function isDefaultAlbum($classroom_id) {
    $album = select_data(
        "album_name",
        "classroom_album",
        "where classroom_id = '{$classroom_id}'"
    );
    
    if ($album && count($album) > 0) {
        $default_names = ['Public', 'Report', 'Delete', 'Restrict', 'Duplicates'];
        return in_array($album[0]['album_name'], $default_names);
    }
    return false;
}
    
    // ============ ฟังก์ชันคำนวณความคล้ายของ Embeddings (Cosine Similarity) ============
    function cosineSimilarity($vec1, $vec2) {
    if (count($vec1) !== count($vec2)) return 0;
    
    $dotProduct = 0;
    $mag1 = 0;
    $mag2 = 0;
    
    for ($i = 0; $i < count($vec1); $i++) {
        $dotProduct += $vec1[$i] * $vec2[$i];
        $mag1 += $vec1[$i] * $vec1[$i];
        $mag2 += $vec2[$i] * $vec2[$i];
    }
    
    $mag1 = sqrt($mag1);
    $mag2 = sqrt($mag2);
    
    if ($mag1 == 0 || $mag2 == 0) return 0;
    
    return $dotProduct / ($mag1 * $mag2);
}

function calculateIoU($box1, $box2) {
    $x1_inter = max($box1['x1'], $box2['x1']);
    $y1_inter = max($box1['y1'], $box2['y1']);
    $x2_inter = min($box1['x2'], $box2['x2']);
    $y2_inter = min($box1['y2'], $box2['y2']);
    
    $inter_width = max(0, $x2_inter - $x1_inter);
    $inter_height = max(0, $y2_inter - $y1_inter);
    $inter_area = $inter_width * $inter_height;
    
    $box1_area = ($box1['x2'] - $box1['x1']) * ($box1['y2'] - $box1['y1']);
    $box2_area = ($box2['x2'] - $box2['x1']) * ($box2['y2'] - $box2['y1']);
    
    $union_area = $box1_area + $box2_area - $inter_area;
    
    if ($union_area == 0) return 0;
    
    return $inter_area / $union_area;
}

function photosAreSimilar($photo1_faces, $photo2_faces, $similarity_threshold = 0.85, $iou_threshold = 0.5) {
    if (count($photo1_faces) !== count($photo2_faces)) return ['similar' => false];  // ✅
    if (count($photo1_faces) == 0) return ['similar' => false];  // ✅
    
    $face_matches = 0;
    $total_similarity = 0;
    
    foreach ($photo1_faces as $face1) {
        foreach ($photo2_faces as $face2) {
            if ($face1['face_index'] !== $face2['face_index']) continue;
            
            $box1 = [
                'x1' => $face1['bbox_x1'],
                'y1' => $face1['bbox_y1'],
                'x2' => $face1['bbox_x2'],
                'y2' => $face1['bbox_y2']
            ];
            $box2 = [
                'x1' => $face2['bbox_x1'],
                'y1' => $face2['bbox_y1'],
                'x2' => $face2['bbox_x2'],
                'y2' => $face2['bbox_y2']
            ];
            
            $iou = calculateIoU($box1, $box2);
            
            if (is_array($face1['embedding_array']) && is_array($face2['embedding_array'])) {
                $similarity = cosineSimilarity($face1['embedding_array'], $face2['embedding_array']);
                
                if ($similarity >= $similarity_threshold && $iou >= $iou_threshold) {
                    $face_matches++;
                    $total_similarity += $similarity;
                }
            }
        }
    }
    
    if ($face_matches == count($photo1_faces) && $face_matches > 0) {
        return [
            'similar' => true,
            'avg_similarity' => $total_similarity / $face_matches,
            'face_matches' => $face_matches
        ];
    }
    
    return ['similar' => false];
}
    
    // ============ Action: Scan for Duplicates ============
    if(isset($_POST['action']) && $_POST['action'] == 'scanDuplicates') {
    $classroom_id = $_POST['classroom_id'];
    
    $SIMILARITY_THRESHOLD = 0.85;
    $IOU_THRESHOLD = 0.5;
    
    // 1. Get all photos with face embeddings
    $photos_with_faces = select_data(
        "DISTINCT photo_id",
        "event_face_embedding",
        "where classroom_id = '{$classroom_id}'"
    );
    
    if (!$photos_with_faces || count($photos_with_faces) == 0) {
        echo json_encode([
            'status' => false,
            'message' => 'No photos with face embeddings found'
        ]);
        exit;
    }
    
    $photo_ids = array_column($photos_with_faces, 'photo_id');
    $photo_ids_str = implode(',', $photo_ids);
    
    // 2. Get all valid photos (not deleted)
    $photos = select_data(
        "photo_id, photo_path",
        "classroom_photo",
        "where photo_id IN ({$photo_ids_str}) and status = 0"
    );
    
    if (!$photos || count($photos) == 0) {
        echo json_encode([
            'status' => false,
            'message' => 'No valid photos found'
        ]);
        exit;
    }
    
    // 3. Build face data map
    $photo_faces = [];
    foreach ($photo_ids as $pid) {
        $faces = select_data(
            "face_id, face_index, bbox_x1, bbox_y1, bbox_x2, bbox_y2, embedding",
            "event_face_embedding",
            "where photo_id = '{$pid}'"
        );
        
        if ($faces && count($faces) > 0) {
            foreach ($faces as &$face) {
                $face['embedding_array'] = json_decode($face['embedding'], true);
            }
            $photo_faces[$pid] = $faces;
        }
    }
    
    // 4. CLUSTERING ALGORITHM - Group similar photos together
    $groups = [];
    $assigned = []; // Track which photos are already in groups
    
    foreach ($photos as $i => $photo1) {
        $photo1_id = $photo1['photo_id'];
        
        if (isset($assigned[$photo1_id])) continue; // Already in a group
        if (!isset($photo_faces[$photo1_id])) continue;
        
        // Start new group with this photo
        $current_group = [$photo1_id];
        $assigned[$photo1_id] = true;
        
        // Find all other photos similar to photos in current group
        $group_expanded = true;
        while ($group_expanded) {
            $group_expanded = false;
            
            foreach ($photos as $j => $photo2) {
                $photo2_id = $photo2['photo_id'];
                
                if (isset($assigned[$photo2_id])) continue;
                if (!isset($photo_faces[$photo2_id])) continue;
                
                // Check if photo2 is similar to ANY photo in current group
                $is_similar_to_group = false;
                $max_similarity = 0;
                
                foreach ($current_group as $group_photo_id) {
                    if (!isset($photo_faces[$group_photo_id])) continue;
                    
                    $result = photosAreSimilar(
                        $photo_faces[$group_photo_id],
                        $photo_faces[$photo2_id],
                        $SIMILARITY_THRESHOLD,
                        $IOU_THRESHOLD
                    );
                    
                    if ($result['similar']) {
                        $is_similar_to_group = true;
                        $max_similarity = max($max_similarity, $result['avg_similarity']);
                        break; // Found match, no need to check other group members
                    }
                }
                
                if ($is_similar_to_group) {
                    $current_group[] = $photo2_id;
                    $assigned[$photo2_id] = true;
                    $group_expanded = true; // Keep expanding
                }
            }
        }
        
        // Only save groups with 2+ photos
        if (count($current_group) >= 2) {
            $groups[] = [
                'photo_ids' => $current_group,
                'photo_count' => count($current_group)
            ];
        }
    }
    
    // 5. Save groups to database
    $saved_groups = 0;
    
    foreach ($groups as $group) {
        $photo_ids_sorted = $group['photo_ids'];
        sort($photo_ids_sorted);
        $group_hash = md5(implode('-', $photo_ids_sorted));
        
        // Check if group already exists
        $existing = select_data(
            "group_id",
            "event_photo_duplicate_group",
            "where classroom_id = '{$classroom_id}' and group_hash = '{$group_hash}'"
        );
        
        if ($existing && count($existing) > 0) {
            $group_id = $existing[0]['group_id'];
            
            // Update existing group
            update_data(
                "event_photo_duplicate_group",
                "photo_count = '{$group['photo_count']}', 
                 status = 1,
                 updated_at = NOW()",
                "group_id = '{$group_id}'"
            );
        } else {
            // Create new group
            $face_count = isset($photo_faces[$group['photo_ids'][0]]) 
                ? count($photo_faces[$group['photo_ids'][0]]) 
                : 0;
            
            $group_id = insert_data(
                "event_photo_duplicate_group",
                "(classroom_id, group_hash, photo_count, face_count, status, created_at, updated_at)",
                "('{$classroom_id}', '{$group_hash}', '{$group['photo_count']}', '{$face_count}', 1, NOW(), NOW())"
            );
        }
        
        if ($group_id) {
            // Clear old members
            delete_data(
                "event_photo_duplicate_member", "group_id = '{$group_id}'"
            );
            
            // Insert all members
            foreach ($group['photo_ids'] as $photo_id) {
                insert_data(
                    "event_photo_duplicate_member",
                    "(group_id, photo_id, similarity_to_group, added_at)",
                    "('{$group_id}', '{$photo_id}', 0.90, NOW())"
                );
            }
            
            $saved_groups++;
        }
    }
    
    echo json_encode([
        'status' => true,
        'duplicate_groups' => $saved_groups,
        'total_photos_in_groups' => array_sum(array_column($groups, 'photo_count'))
    ]);
    exit;
}

// ============================================================
// ACTION: Get Duplicate Groups
// ============================================================

if(isset($_POST['action']) && $_POST['action'] == 'getDuplicateGroups') {
    $classroom_id = $_POST['classroom_id'];
    
    // Get all pending groups
    $groups = select_data(
        "g.group_id, g.photo_count, g.avg_similarity, g.face_count,
         DATE_FORMAT(g.updated_at, '%Y/%m/%d %H:%i:%s') as updated_at",
        "event_photo_duplicate_group g",
        "where g.classroom_id = '{$classroom_id}' and g.status = 1
         order by g.photo_count desc, g.updated_at desc"
    );
    
    $group_data = [];
    
    if ($groups && count($groups) > 0) {
        foreach ($groups as $group) {
            // Get all active members (not deleted, photo not deleted)
            $members = select_data(
                "m.member_id, m.photo_id, m.similarity_to_group,
                 p.photo_path",
                "event_photo_duplicate_member m
                 LEFT JOIN event_photo p ON m.photo_id = p.photo_id",
                "where m.group_id = '{$group['group_id']}' 
                 and m.is_deleted = 0 
                 and p.status = 0
                 order by m.similarity_to_group desc"
            );
            
            if (!$members || count($members) < 2) {
                // Auto-resolve groups with less than 2 photos
                update_data(
                    "event_photo_duplicate_group",
                    "status = 0, updated_at = NOW()",
                    "group_id = '{$group['group_id']}'"
                );
                continue;
            }
            
            // Build member data
            $member_data = [];
            foreach ($members as $member) {
                $path_parts = pathinfo($member['photo_path']);
                $thumb = $path_parts['dirname'] . '/' . $path_parts['filename'] . '_thumbnail.' . $path_parts['extension'];
                
                $member_data[] = [
                    'member_id' => $member['member_id'],
                    'photo_id' => $member['photo_id'],
                    'photo_thumb' => GetPublicUrl($thumb),
                    'photo_full' => GetPublicUrl($member['photo_path']),
                    'similarity' => round($member['similarity_to_group'] * 100, 2)
                ];
            }
            
            $group_data[] = [
                'group_id' => $group['group_id'],
                'photo_count' => count($member_data),
                'face_count' => $group['face_count'],
                'updated_at' => $group['updated_at'],
                'members' => $member_data
            ];
        }
    }
    
    echo json_encode([
        'status' => true,
        'groups' => $group_data
    ]);
    exit;
}

// ============================================================
// ACTION: Mark Group as Not Duplicate
// ============================================================

if(isset($_POST['action']) && $_POST['action'] == 'markGroupNotDuplicate') {
    $group_id = $_POST['group_id'];
    
    update_data(
        "event_photo_duplicate_group",
        "status = 2, updated_at = NOW()",
        "group_id = '{$group_id}'"
    );
    
    echo json_encode([
        'status' => true,
        'message' => 'Group marked as not duplicate'
    ]);
    exit;
}

// ============================================================
// ACTION: Delete Photo from Group
// ============================================================

if(isset($_POST['action']) && $_POST['action'] == 'deletePhotoFromGroup') {
    $group_id = $_POST['group_id'];
    $photo_id = $_POST['photo_id'];
    
    // Delete the photo (soft delete)
    update_data(
        "classroom_photo",
        "status = 1, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
        "photo_id = '{$photo_id}'"
    );
    
    // Mark as deleted in group
    update_data(
        "event_photo_duplicate_member",
        "is_deleted = 1",
        "group_id = '{$group_id}' and photo_id = '{$photo_id}'"
    );
    
    // Check if group still has enough photos
    $remaining = select_data(
        "COUNT(*) as total",
        "event_photo_duplicate_member m
         LEFT JOIN event_photo p ON m.photo_id = p.photo_id",
        "where m.group_id = '{$group_id}' and m.is_deleted = 0 and p.status = 0"
    );
    
    $remaining_count = $remaining[0]['total'] ? $remaining[0]['total'] : 0;
    
    if ($remaining_count < 2) {
        // Auto-resolve group
        update_data(
            "event_photo_duplicate_group",
            "status = 0, updated_at = NOW()",
            "group_id = '{$group_id}'"
        );
    }
    
    echo json_encode([
        'status' => true,
        'remaining_count' => $remaining_count,
        'message' => 'Photo deleted successfully'
    ]);
    exit;
}

// ============================================================
// ACTION: Delete ALL Photos in Group (Keep One)
// ============================================================

if(isset($_POST['action']) && $_POST['action'] == 'deleteGroupKeepOne') {
    $group_id = $_POST['group_id'];
    $keep_photo_id = $_POST['keep_photo_id'];
    
    // Get all members
    $members = select_data(
        "photo_id",
        "event_photo_duplicate_member",
        "where group_id = '{$group_id}' and is_deleted = 0"
    );
    
    $deleted_count = 0;
    
    foreach ($members as $member) {
        if ($member['photo_id'] == $keep_photo_id) continue;
        
        // Delete photo
        update_data(
            "classroom_photo",
            "status = 1, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
            "photo_id = '{$member['photo_id']}'"
        );
        
        // Mark as deleted in group
        update_data(
            "event_photo_duplicate_member",
            "is_deleted = 1",
            "group_id = '{$group_id}' and photo_id = '{$member['photo_id']}'"
        );
        
        $deleted_count++;
    }
    
    // Resolve group
    update_data(
        "event_photo_duplicate_group",
        "status = 0, updated_at = NOW()",
        "group_id = '{$group_id}'"
    );
    
     echo json_encode([
        'status' => true,
        'deleted_count' => $deleted_count,
        'message' => "Deleted {$deleted_count} photos, kept 1"
    ]);
    exit;
}
    
    // ============ Action: Sync Default Albums (ไม่ต้องใช้แล้ว แต่เก็บไว้เพื่อ compatibility) ============
    if(isset($_POST['action']) && $_POST['action'] == 'syncDefaultAlbums') {
        $classroom_id = $_POST['classroom_id'];
        $comp_id = $_SESSION['comp_id'];
        $emp_id = $_SESSION['emp_id'];
        
        ensureDefaultAlbums($classroom_id, $comp_id, $emp_id);
        
        echo json_encode([
            'status' => true,
            'moved_count' => 0,
            'message' => 'Default albums are now virtual - images are auto-filtered by status'
        ]);
        exit;
    }
    
    if(isset($_POST['action']) && $_POST['action'] == 'albumData') {
        $classroom_id = $_POST['classroom_id'];
        $album = select_data(
            "album_name, album_description", "classroom_album", "where classroom_id = '{$classroom_id}'"
        );
        echo json_encode([
            'status' => true,
            'album_data' => [
                'album_name' => $album[0]['album_name'],
                'album_description' => $album[0]['album_description']
            ],
        ]);
        exit;
    }
    
    if(isset($_POST['action']) && $_POST['action'] == 'deleteAlbum') {
        $classroom_id = $_POST['classroom_id'];
        
        $album_check = select_data(
            "album_name",
            "classroom_album",
            "where classroom_id = '{$classroom_id}'"
        );
        
        if ($album_check && count($album_check) > 0) {
            $album_name = $album_check[0]['album_name'];
            $default_names = ['Public', 'Report', 'Delete', 'Restrict', 'Duplicates'];
            
            if (in_array($album_name, $default_names)) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Cannot delete default album: ' . $album_name
                ]);
                exit;
            }
        }
        
        update_data(
            "classroom_album", 
            "status = 1, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()", 
            "classroom_id = '{$classroom_id}'"
        );
        
        update_data(
            "classroom_photo",
            "status = 1, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
            "classroom_id = '{$classroom_id}'"
        );
        
        echo json_encode([
            'status' => true,
        ]);
        exit;
    }
    
    if(isset($_GET['action']) && $_GET['action'] == 'saveAlbum') {
        $classroom_id = $_POST['classroom_id'];
        $classroom_id = $_POST['classroom_id'];
        $album_name = escape_string($_POST['album_name']);
        $album_description = escape_string($_POST['album_description']);
        
        if (!$classroom_id) {
            $default_names = ['Public', 'Report', 'Delete', 'Restrict', 'Duplicates'];
            if (in_array($album_name, $default_names)) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Album name "' . $album_name . '" is reserved. Please use another name.'
                ]);
                exit;
            }
        }
        
        if($classroom_id) {
            $album_check = select_data(
                "album_name",
                "classroom_album",
                "where classroom_id = '{$classroom_id}'"
            );
            
            if ($album_check && count($album_check) > 0) {
                $current_name = $album_check[0]['album_name'];
                $default_names = ['Public', 'Report', 'Delete', 'Restrict', 'Duplicates'];
                
                if (in_array($current_name, $default_names)) {
                    echo json_encode([
                        'status' => false,
                        'message' => 'Cannot edit default album: ' . $current_name
                    ]);
                    exit;
                }
            }
            
            update_data(
                "classroom_album", 
                "album_name = '{$album_name}', album_description = '{$album_description}', emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()", 
                "classroom_id = '{$classroom_id}'"
            );
        } else {
            insert_data(
                "classroom_album", 
                "(classroom_id, comp_id, album_name, album_description, status, emp_create, date_create, emp_modify, date_modify)", 
                "('{$classroom_id}', '{$_SESSION['comp_id']}', '{$album_name}', '{$album_description}', 0, '{$_SESSION['emp_id']}', NOW(), '{$_SESSION['emp_id']}', NOW())"
            );
        }
        echo json_encode([
            'status' => true,
        ]);
        exit;
    }

    if(isset($_POST['action']) && $_POST['action'] == 'restoreImage') {
        $photo_id = $_POST['photo_id'];
        
        update_data(
            "classroom_photo", 
            "status = 0, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()", 
            "photo_id = '{$photo_id}'"
        );
        
        update_data(
            "event_photo_queue",
            "status = 'pending', updated_at = NOW()",
            "photo_id = '{$photo_id}'"
        );
        
        echo json_encode([
            'status' => true,
            'message' => 'Image restored successfully'
        ]);
        exit;
    }
    
    if(isset($_POST['action']) && $_POST['action'] == 'buildAlbum') {
        $classroom_id = $_POST['classroom_id'];
        
        ensureDefaultAlbums($classroom_id, $_SESSION['comp_id'], $_SESSION['emp_id']);
        
        $albums = select_data(
            "classroom_id, album_name, album_description, date_format(date_modify, '%Y/%m/%d %H:%i:%s') as date_modify", 
            "classroom_album", 
            "where classroom_id = '{$classroom_id}' and status = 0 order by 
                CASE 
                    WHEN album_name = 'Public' THEN 1
                    WHEN album_name = 'Report' THEN 2
                    WHEN album_name = 'Restrict' THEN 3
                    WHEN album_name = 'Delete' THEN 4
                    WHEN album_name = 'Duplicates' THEN 5
                    ELSE 6
                END,
                date_modify desc"
        );
        
        $album_data = [];
        $default_names = ['Public', 'Report', 'Delete', 'Restrict', 'Duplicates'];
        
        foreach($albums as $album) {
            $is_default = in_array($album['album_name'], $default_names);
            
            if ($is_default) {
                if ($album['album_name'] == 'Duplicates') {
    // นับจำนวน GROUPS ที่ยังไม่ได้ตรวจสอบ (status = 1)
    $groups = select_data(
        "COUNT(*) as total",
        "event_photo_duplicate_group",
        "where classroom_id = '{$classroom_id}' and status = 1"
    );
    $image_count = $groups[0]['total'] ? $groups[0]['total'] : 0;
    
    // ดึงรูป cover จาก group แรก
    $first_group = select_data(
        "g.group_id",
        "event_photo_duplicate_group g",
        "where g.classroom_id = '{$classroom_id}' and g.status = 1
         order by g.updated_at desc limit 1"
    );
    
    if ($first_group && count($first_group) > 0) {
        $group_id = $first_group[0]['group_id'];
        $first_photo = select_data(
            "p.photo_path",
            "event_photo_duplicate_member m
             LEFT JOIN event_photo p ON m.photo_id = p.photo_id",
            "where m.group_id = '{$group_id}' and m.is_deleted = 0 and p.status = 0
             order by m.added_at asc limit 1"
        );
        $cover = $first_photo;
    } else {
        $cover = null;
    }
} else {
                    $where_clause = "where classroom_id = '{$classroom_id}'";
                    
                    switch($album['album_name']) {
                        case 'Public':
                            $where_clause .= " and public = 0 and status = 0 and photo_id NOT IN (SELECT DISTINCT photo_id FROM event_photo_report)";
                            break;
                        case 'Report':
                            $where_clause .= " and status = 0 and photo_id IN (SELECT DISTINCT photo_id FROM event_photo_report)";
                            break;
                        case 'Delete':
                            $where_clause .= " and status = 1";
                            break;
                        case 'Restrict':
                            $where_clause .= " and public = 1 and status = 0 and photo_id NOT IN (SELECT DISTINCT photo_id FROM event_photo_report)";
                            break;
                    }
                    
                    $image = select_data("photo_id", "classroom_photo", $where_clause);
                    $image_count = count($image);
                    $cover = select_data("photo_path", "classroom_photo", "{$where_clause} order by date_create asc limit 1");
                }
            } else {
                $image = select_data("photo_id", "classroom_photo", "where classroom_id = '{$album['classroom_id']}' and status = 0");
                $image_count = count($image);
                $cover = select_data("photo_path", "classroom_photo", "where classroom_id = '{$album['classroom_id']}' and status = 0 order by date_create asc limit 1");
            }
            
            $cover_image = '';
            if($cover && count($cover) > 0) {
                $path_parts = pathinfo($cover[0]['photo_path']);
                $thumbnail_path = $path_parts['dirname'] . '/' . $path_parts['filename'] . '_thumbnail.' . $path_parts['extension'];
                $cover_image = GetPublicUrl($thumbnail_path);
            }
            
            $album_data[] = [
                'classroom_id' => $album['classroom_id'],
                'album_name' => $album['album_name'],
                'album_description' => $album['album_description'],
                'date_modify' => $album['date_modify'],
                'image_count' => $image_count,
                'cover_image' => $cover_image,
                'is_default' => $is_default
            ];
        }
        
        echo json_encode([
            'status' => true,
            'album_data' => $album_data
        ]);
        exit;
    }
    
    if(isset($_POST['action']) && $_POST['action'] == 'moveImages') {
        $photo_ids = $_POST['photo_ids'];
        $target_classroom_id = $_POST['target_classroom_id'];
        
        $moved_count = 0;
        
        foreach ($photo_ids as $photo_id) {
            update_data(
                "classroom_photo",
                "classroom_id = '{$target_classroom_id}', emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
                "photo_id = '{$photo_id}'"
            );
            $moved_count++;
        }
        
        echo json_encode([
            'status' => true,
            'moved_count' => $moved_count,
            'skipped_count' => 0
        ]);
        exit;
    }
    
    if(isset($_POST['action']) && $_POST['action'] == 'togglePublic') {
        $photo_id = $_POST['photo_id'];
        $public_status = $_POST['public_status'];
        
        update_data(
            "classroom_photo", 
            "public = '{$public_status}', emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()", 
            "photo_id = '{$photo_id}'"
        );
        
        echo json_encode([
            'status' => true,
            'public_status' => $public_status
        ]);
        exit;
    }

    if(isset($_POST['action']) && $_POST['action'] == 'getReportDetails') {
        $photo_id = $_POST['photo_id'];
        $classroom_id = $_POST['classroom_id'];

        if (!$classroom_id || !$photo_id) {
            echo json_encode([
                'status' => false,
                'message' => 'Invalid parameters'
            ]);
            exit;
        }
        
        $photo_info = select_data(
            "public",
            "classroom_photo",
            "where photo_id = '{$photo_id}'"
        );
        $photo_public = $photo_info[0]['public'];
        
        $reports = select_data(
            "r.report_id, r.report_reason, r.user_id as member_id,
             DATE_FORMAT(r.created_at, '%Y/%m/%d %H:%i:%s') as created_at, 
             CONCAT(COALESCE(m.firstname, ''), ' ', COALESCE(m.lastname, '')) as full_name",
            "event_photo_report r 
             LEFT JOIN event_members m ON r.user_id = m.member_id",
            "where r.photo_id = '{$photo_id}' order by r.created_at desc"
        );
        
        foreach ($reports as &$report) {
            $member_id = $report['member_id'];
            if ($member_id) {
                $register_data = select_data(
                    "register_id",
                    "event_register",
                    "where classroom_id = '{$classroom_id}' and user_id = '{$member_id}' and status = 0 limit 1"
                );
                
                if ($register_data && count($register_data) > 0) {
                    $register_id = $register_data[0]['register_id'];
                    $report['profile_image_url'] = guestImage($classroom_id, $register_id);
                } else {
                    $report['profile_image_url'] = '';
                }
            } else {
                $report['profile_image_url'] = '';
            }
            unset($report['member_id']);
        }
        unset($report);

        echo json_encode([
            'status' => true,
            'reports' => $reports,
            'photo_public' => $photo_public
        ]);
        exit;
    }

    if(isset($_POST['action']) && $_POST['action'] == 'getDownloadDetails') {
        $photo_id = $_POST['photo_id'];
        
        $photo_data = select_data(
            "classroom_id",
            "classroom_photo",
            "where photo_id = '{$photo_id}'"
        );
        
        if (!$photo_data || count($photo_data) == 0) {
            echo json_encode([
                'status' => false,
                'message' => 'Photo not found'
            ]);
            exit;
        }
        
        $classroom_id = $photo_data[0]['classroom_id'];

        $downloads = select_data(
            "d.download_id, d.download_count, d.user_id as member_id,
             DATE_FORMAT(d.downloaded_at, '%Y/%m/%d %H:%i:%s') as downloaded_at,
             CONCAT(COALESCE(m.firstname, ''), ' ', COALESCE(m.lastname, '')) as full_name",
            "event_photo_download d 
             LEFT JOIN event_members m ON d.user_id = m.member_id",
            "where d.photo_id = '{$photo_id}' order by d.downloaded_at desc"
        );
        
        foreach ($downloads as &$dl) {
            $member_id = $dl['member_id'];
            if ($member_id) {
                $register_data = select_data(
                    "register_id",
                    "event_register",
                    "where classroom_id = '{$classroom_id}' and user_id = '{$member_id}' and status = 0 limit 1"
                );
                
                if ($register_data && count($register_data) > 0) {
                    $register_id = $register_data[0]['register_id'];
                    $dl['profile_image_url'] = guestImage($classroom_id, $register_id);
                } else {
                    $dl['profile_image_url'] = '';
                }
            } else {
                $dl['profile_image_url'] = '';
            }
            unset($dl['member_id']);
        }
        unset($dl);

        echo json_encode([
            'status' => true,
            'downloads' => $downloads
        ]);
        exit;
    }

    if(isset($_POST['action']) && $_POST['action'] == 'buildImage') {
    $classroom_id = $_POST['classroom_id'];
    
    $album_info = select_data(
        "album_name, classroom_id",
        "classroom_album",
        "where classroom_id = '{$classroom_id}'"
    );
    
    if (!$album_info || count($album_info) == 0) {
        echo json_encode([
            'status' => false,
            'message' => 'Album not found'
        ]);
        exit;
    }
    
    $album_name = $album_info[0]['album_name'];
    $classroom_id = $album_info[0]['classroom_id'];
    $is_default = in_array($album_name, ['Public', 'Report', 'Delete', 'Restrict', 'Duplicates']);
    
    // ===== ถ้าเป็น Duplicates Album ให้ดึงรูปจากคู่ =====
    if ($album_name == 'Duplicates') {
        // ดึงรูปทั้งหมดที่อยู่ในคู่ duplicate
        $duplicate_photos = select_data(
            "DISTINCT p.photo_id, p.photo_path, p.public, p.status, p.classroom_id, 
             DATE_FORMAT(p.date_create, '%Y/%m/%d %H:%i:%s') as date_create",
            "event_photo_duplicate_check c
             INNER JOIN event_photo p ON (c.photo_id_1 = p.photo_id OR c.photo_id_2 = p.photo_id)",
            "where c.classroom_id = '{$classroom_id}' 
             and c.is_duplicate = 1 
             and p.status = 0
             order by p.date_create desc"
        );
        
        if (!$duplicate_photos || count($duplicate_photos) == 0) {
            echo json_encode([
                'status' => true,
                'image_data' => [],
                'is_default_album' => true,
                'album_name' => 'Duplicates',
                'is_duplicate_album' => false  // แสดงแบบปกติ
            ]);
            exit;
        }
        
        // สร้าง image data เหมือนอัลบัมปกติ
        $image_data = [];
        foreach($duplicate_photos as $image) {
            $path_parts = pathinfo($image['photo_path']);
            $thumbnail_path = $path_parts['dirname'] . '/' . $path_parts['filename'] . '_thumbnail.' . $path_parts['extension'];
            
            $queue_data = select_data(
                "status, error_msg, thumbnail_300_path", 
                "event_photo_resize_queue", 
                "where photo_id = '{$image['photo_id']}'"
            );
            
            $queue_status = 'not_found';
            $error_msg = '';
            $thumbnail_300_path = '';
            
            if($queue_data && count($queue_data) > 0) {
                $queue_status = $queue_data[0]['status'];
                $error_msg = $queue_data[0]['error_msg'];
                $thumbnail_300_path = $queue_data[0]['thumbnail_300_path'];
            }
            
            $report_count = select_data(
                "COUNT(*) as total",
                "event_photo_report",
                "where photo_id = '{$image['photo_id']}'"
            );
            $total_reports = $report_count[0]['total'] ? $report_count[0]['total'] : 0;
            
            $download_count = select_data(
                "SUM(download_count) as total",
                "event_photo_download",
                "where photo_id = '{$image['photo_id']}'"
            );
            $total_downloads = $download_count[0]['total'] ? $download_count[0]['total'] : 0;
            
            // หาว่ารูปนี้อยู่ในอัลบัมจริงไหน
            $real_album_name = '';
            if ($image['classroom_id']) {
                $real_album = select_data(
                    "album_name",
                    "classroom_album",
                    "where classroom_id = '{$image['classroom_id']}'"
                );
                if ($real_album && count($real_album) > 0) {
                    $real_album_name = $real_album[0]['album_name'];
                }
            }
            
            // หาว่ารูปนี้มีคู่ที่ซ้ำกับใครบ้าง
            $pair_info = select_data(
                "c.similarity_score, c.face_match_count,
                 CASE 
                   WHEN c.photo_id_1 = '{$image['photo_id']}' THEN c.photo_id_2
                   ELSE c.photo_id_1
                 END as pair_photo_id",
                "event_photo_duplicate_check c",
                "where c.classroom_id = '{$classroom_id}' 
                 and c.is_duplicate = 1
                 and (c.photo_id_1 = '{$image['photo_id']}' OR c.photo_id_2 = '{$image['photo_id']}')"
            );
            
            $similarity = 0;
            $face_matches = 0;
            $pair_photo_id = '';
            if ($pair_info && count($pair_info) > 0) {
                $similarity = round($pair_info[0]['similarity_score'] * 100, 2);
                $face_matches = $pair_info[0]['face_match_count'];
                $pair_photo_id = $pair_info[0]['pair_photo_id'];
            }
            
            $image_data[] = [
                'photo_id' => $image['photo_id'],
                'photo_path' => GetPublicUrl($thumbnail_path),
                'thumbnail_300_path' => $thumbnail_300_path ? GetPublicUrl($thumbnail_300_path) : '',
                'public' => $image['public'],
                'status' => $image['status'],
                'queue_status' => $queue_status,
                'error_msg' => $error_msg,
                'report_count' => $total_reports,
                'download_count' => $total_downloads,
                'date_create' => $image['date_create'],
                'real_album_name' => $real_album_name,
                'similarity' => $similarity,  // เพิ่มข้อมูลความคล้าย
                'face_matches' => $face_matches,  // เพิ่มจำนวน face ที่ตรง
                'pair_photo_id' => $pair_photo_id  // รูปคู่
            ];
        }
        
        echo json_encode([
            'status' => true,
            'image_data' => $image_data,
            'is_default_album' => true,
            'album_name' => 'Duplicates',
            'is_duplicate_album' => false  // แสดงแบบตารางปกติ
        ]);
        exit;
    }
        
        if ($is_default) {
            if ($album_name == 'Duplicates') {
                // ส่งข้อมูลคู่รูปซ้ำแทน
                echo json_encode([
                    'status' => true,
                    'is_default_album' => true,
                    'album_name' => 'Duplicates',
                    'is_duplicate_album' => true
                ]);
                exit;
            }
            
            $where_clause = "where classroom_id = '{$classroom_id}'";
            
            switch($album_name) {
                case 'Delete':
                    $where_clause .= " and status = 1";
                    break;
                case 'Restrict':
                    $where_clause .= " and public = 1 and status = 0";
                    break;
                case 'Report':
                    $where_clause .= " and public = 0 and status = 0";
                    $where_clause .= " and photo_id IN (SELECT DISTINCT photo_id FROM event_photo_report)";
                    break;
                case 'Public':
                    $where_clause .= " and public = 0 and status = 0";
                    $where_clause .= " and photo_id NOT IN (SELECT DISTINCT photo_id FROM event_photo_report)";
                    break;
            }
        } else {
            $where_clause = "where classroom_id = '{$classroom_id}' and status = 0";
        }
        
        $images = select_data(
            "photo_id, photo_path, public, status, classroom_id, date_format(date_create, '%Y/%m/%d %H:%i:%s') as date_create", 
            "classroom_photo", 
            "{$where_clause} order by date_create desc"
        );
        
        $image_data = [];
        foreach($images as $image) {
            $path_parts = pathinfo($image['photo_path']);
            $thumbnail_path = $path_parts['dirname'] . '/' . $path_parts['filename'] . '_thumbnail.' . $path_parts['extension'];
            
            $queue_data = select_data(
                "status, error_msg, thumbnail_300_path", 
                "event_photo_resize_queue", 
                "where photo_id = '{$image['photo_id']}'"
            );
            
            $queue_status = 'not_found';
            $error_msg = '';
            $thumbnail_300_path = '';
            
            if($queue_data && count($queue_data) > 0) {
                $queue_status = $queue_data[0]['status'];
                $error_msg = $queue_data[0]['error_msg'];
                $thumbnail_300_path = $queue_data[0]['thumbnail_300_path'];
            }
            
            $report_count = select_data(
                "COUNT(*) as total",
                "event_photo_report",
                "where photo_id = '{$image['photo_id']}'"
            );
            $total_reports = $report_count[0]['total'] ? $report_count[0]['total'] : 0;
            
            $download_count = select_data(
                "SUM(download_count) as total",
                "event_photo_download",
                "where photo_id = '{$image['photo_id']}'"
            );
            $total_downloads = $download_count[0]['total'] ? $download_count[0]['total'] : 0;
            
            $real_album_name = '';
            if ($is_default && $image['classroom_id']) {
                $real_album = select_data(
                    "album_name",
                    "classroom_album",
                    "where classroom_id = '{$image['classroom_id']}'"
                );
                if ($real_album && count($real_album) > 0) {
                    $real_album_name = $real_album[0]['album_name'];
                }
            }
            
            $image_data[] = [
                'photo_id' => $image['photo_id'],
                'photo_path' => GetPublicUrl($thumbnail_path),
                'thumbnail_300_path' => $thumbnail_300_path ? GetPublicUrl($thumbnail_300_path) : '',
                'public' => $image['public'],
                'status' => $image['status'],
                'queue_status' => $queue_status,
                'error_msg' => $error_msg,
                'report_count' => $total_reports,
                'download_count' => $total_downloads,
                'date_create' => $image['date_create'],
                'real_album_name' => $real_album_name
            ];
        }
        
        echo json_encode([
            'status' => true,
            'image_data' => $image_data,
            'is_default_album' => $is_default,
            'album_name' => $album_name
        ]);
        exit;
    }
    
    if(isset($_POST['action']) && $_POST['action'] == 'deleteImage') {
        $photo_id = $_POST['photo_id'];
        
        update_data(
            "classroom_photo", 
            "status = 1, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()", 
            "photo_id = '{$photo_id}'"
        );
        
        update_data(
            "event_photo_queue",
            "status = 'deleted', updated_at = NOW()",
            "photo_id = '{$photo_id}'"
        );
        
        echo json_encode([
            'status' => true,
            'message' => 'Image deleted successfully'
        ]);
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'uploadImages') {
        $classroom_id = $_POST['classroom_id'];
        $classroom_id = $_POST['classroom_id'];
        $comp_id = $_SESSION['comp_id'];
        $emp_id = $_SESSION['emp_id'];
        
        $MAX_FILE_SIZE = 100 * 1024 * 1024;
        $ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'bmp'];
        
        $upload_dir = "uploads/{$comp_id}/events/{$classroom_id}/gallery/";
        $errors = [];
        $success_count = 0;
        $detailed_errors = [];
        
        $event_data = select_data("comp_id", "event_template", "where id = '{$classroom_id}'");
        $logo_temp = null;
        $logo_error = null;
        
        if ($event_data && count($event_data) > 0) {
            $event_comp_id = $event_data[0]['comp_id'];
            $company_data = select_data("comp_logo", "m_company", "where comp_id = '{$event_comp_id}'");
            
            if ($company_data && count($company_data) > 0 && !empty($company_data[0]['comp_logo'])) {
                $event_logo_url = '/' . $company_data[0]['comp_logo'];
                $logo_file_path = $_SERVER['DOCUMENT_ROOT'] . $event_logo_url;
                
                if (file_exists($logo_file_path)) {
                    $logo_temp = sys_get_temp_dir() . '/event_logo_' . md5($event_logo_url) . '.png';
                    if (!file_exists($logo_temp)) {
                        if (!copy($logo_file_path, $logo_temp)) {
                            $logo_error = "Cannot copy logo file";
                            $logo_temp = null;
                        } else {
                            $logo_info = @getimagesize($logo_temp);
                            if ($logo_info === false) {
                                @unlink($logo_temp);
                                $logo_temp = null;
                                $logo_error = "Logo is not valid image";
                            }
                        }
                    }
                } else {
                    $logo_error = "Logo file not found at: {$logo_file_path}";
                }
            }
        }
        
        foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
            $file_name = $_FILES['files']['name'][$key];
            $file_size = $_FILES['files']['size'][$key];
            $file_error = $_FILES['files']['error'][$key];
            
            if ($file_error !== UPLOAD_ERR_OK) {
                $error_messages = [
                    UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                    UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                    UPLOAD_ERR_PARTIAL => 'File uploaded partially',
                    UPLOAD_ERR_NO_FILE => 'No file uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temp folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
                    UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
                ];
                $errors[] = "'{$file_name}': " . ($error_messages[$file_error] ? $error_messages[$file_error] : "Unknown error {$file_error}");
                $detailed_errors['upload_error'][] = $file_name;
                continue;
            }
            
            $path_info = pathinfo($file_name);
            $ext = strtolower($path_info['extension']);
            if (!in_array($ext, $ALLOWED_EXTENSIONS)) {
                $errors[] = "'{$file_name}': Invalid extension '{$ext}'";
                $detailed_errors['invalid_extension'][] = $file_name;
                continue;
            }
            
            if ($file_size > $MAX_FILE_SIZE) {
                $size_mb = round($file_size / (1024 * 1024), 2);
                $errors[] = "'{$file_name}': Too large ({$size_mb}MB)";
                $detailed_errors['too_large'][] = $file_name;
                continue;
            }
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $tmp_name);
            finfo_close($finfo);
            
            $allowed_mimes = ['image/jpeg', 'image/png', 'image/bmp', 'image/x-ms-bmp'];
            if (!in_array($mime_type, $allowed_mimes)) {
                $errors[] = "'{$file_name}': Invalid MIME type '{$mime_type}'";
                $detailed_errors['invalid_mime'][] = $file_name;
                continue;
            }
            
            $image_info = @getimagesize($tmp_name);
            if ($image_info === false) {
                $errors[] = "'{$file_name}': Not a valid image file";
                $detailed_errors['invalid_image'][] = $file_name;
                continue;
            }
            
            $file_base_name = $path_info['filename'];
            $clean_name = preg_replace("/[^a-zA-Z0-9\.\-\_]/", "_", $file_base_name);
            $strname = uniqid() . "_" . $clean_name;
            
            // ============================================================
            // 🆕 STEP 1: บันทึกรูปต้นฉบับ (ไม่มีลายน้ำ) ก่อน
            // ============================================================
            $original_no_logo = $upload_dir . $strname . '_original.' . $ext;
            try {
                $save_original_result = SaveFile($tmp_name, $original_no_logo);
                
                if (!$save_original_result) {
                    $errors[] = "'{$file_name}': Cannot save original (no watermark) to storage";
                    $detailed_errors['storage_save_failed'][] = $file_name;
                    continue;
                }
            } catch (Exception $e) {
                $errors[] = "'{$file_name}': Storage error (original) - {$e->getMessage()}";
                $detailed_errors['storage_exception'][] = $file_name;
                continue;
            }
            
            // ============================================================
            // 🆕 STEP 2: สร้างเวอร์ชันที่มีลายน้ำ
            // ============================================================
            $temp_with_logo = sys_get_temp_dir() . '/' . uniqid('original_logo_') . '.' . $ext;
            if (!copy($tmp_name, $temp_with_logo)) {
                $errors[] = "'{$file_name}': Cannot create temp file";
                $detailed_errors['temp_copy_failed'][] = $file_name;
                continue;
            }
            
            $logo_added = false;
            if ($logo_temp !== null && file_exists($logo_temp)) {
                $logo_result = addEventLogoToOriginal($temp_with_logo, $logo_temp);
                if (!$logo_result['success']) {
                    $errors[] = "'{$file_name}': Logo not added - {$logo_result['error']} (using original)";
                    $detailed_errors['logo_failed'][] = $file_name;
                    copy($tmp_name, $temp_with_logo);
                } else {
                    $logo_added = true;
                }
            }
            
            // ============================================================
            // 🆕 STEP 3: บันทึกเวอร์ชันที่มีลายน้ำ
            // ============================================================
            try {
                $original_image = $upload_dir . $strname . '.' . $ext;
                $save_result = SaveFile($temp_with_logo, $original_image);
                
                if (!$save_result) {
                    $errors[] = "'{$file_name}': Cannot save watermarked version to storage";
                    $detailed_errors['storage_save_failed'][] = $file_name;
                    @unlink($temp_with_logo);
                    continue;
                }
            } catch (Exception $e) {
                $errors[] = "'{$file_name}': Storage error (watermarked) - {$e->getMessage()}";
                $detailed_errors['storage_exception'][] = $file_name;
                @unlink($temp_with_logo);
                continue;
            }
            
            $temp_base = sys_get_temp_dir() . "/event_originals/{$classroom_id}/";
            if (!is_dir($temp_base)) {
                if (!mkdir($temp_base, 0777, true)) {
                    $errors[] = "'{$file_name}': Cannot create temp directory";
                    $detailed_errors['temp_dir_failed'][] = $file_name;
                    @unlink($temp_with_logo);
                    continue;
                }
            }
            
            $temp_original_path = $temp_base . $strname . '.' . $ext;
            if (!copy($temp_with_logo, $temp_original_path)) {
                $errors[] = "'{$file_name}': Cannot copy to temp queue";
                $detailed_errors['temp_queue_failed'][] = $file_name;
                @unlink($temp_with_logo);
                continue;
            }
            @chmod($temp_original_path, 0777);
            @unlink($temp_with_logo);
            
            try {
                $photo_path = escape_string($original_image);
                $photo_name = escape_string($file_base_name);
                
                $photo_id = insert_data(
                    "classroom_photo",
                    "(classroom_id, classroom_id, comp_id, photo_name, photo_path, public, status, emp_create, date_create, emp_modify, date_modify)",
                    "('{$classroom_id}', '{$classroom_id}', '{$comp_id}', '{$photo_name}', '{$photo_path}', 0, 0, '{$emp_id}', NOW(), '{$emp_id}', NOW())"
                );
                
                if (!$photo_id) {
                    $errors[] = "'{$file_name}': Cannot insert to database";
                    $detailed_errors['db_insert_failed'][] = $file_name;
                    continue;
                }
                
                $temp_original_path_escaped = escape_string($temp_original_path);
                $queue_result = insert_data(
                    "event_photo_resize_queue",
                    "(photo_id, classroom_id, original_path, temp_original_path, status, created_at, updated_at)",
                    "('{$photo_id}', '{$classroom_id}', '{$photo_path}', '{$temp_original_path_escaped}', 'pending', NOW(), NOW())"
                );
                
                if (!$queue_result) {
                    $errors[] = "'{$file_name}': Cannot insert to queue";
                    $detailed_errors['queue_insert_failed'][] = $file_name;
                }
                
                $success_count++;
                
            } catch (Exception $e) {
                $errors[] = "'{$file_name}': Database error - {$e->getMessage()}";
                $detailed_errors['db_exception'][] = $file_name;
                continue;
            }
        }
        
        $error_summary = [];
        if (!empty($detailed_errors)) {
            foreach ($detailed_errors as $type => $files) {
                $error_summary[] = ucfirst(str_replace('_', ' ', $type)) . ": " . count($files);
            }
        }
        
        echo json_encode([
            'status' => true,
            'success_count' => $success_count,
            'total_files' => count($_FILES['files']['tmp_name']),
            'failed_count' => count($_FILES['files']['tmp_name']) - $success_count,
            'errors' => $errors,
            'error_summary' => $error_summary,
            'detailed_errors' => $detailed_errors,
            'logo_status' => [
                'logo_available' => ($logo_temp !== null),
                'logo_error' => $logo_error
            ]
        ]);
        exit;
    }

    function getRegisterIdFromMember($classroom_id, $member_id) {
        $register = select_data(
            "register_id",
            "event_register",
            "where id = '{$classroom_id}' and user_id = '{$member_id}' and status = 0"
        );
        if ($register && count($register) > 0) {
            return $register[0]['register_id'];
        }
        return null;
    }

function addEventLogoToOriginal($image_path, $logo_path) {
        try {
            if (!file_exists($image_path)) {
                return ['success' => false, 'error' => 'Image not found'];
            }
            if (!file_exists($logo_path)) {
                return ['success' => false, 'error' => 'Logo not found'];
            }
            
            if (extension_loaded('imagick')) {
                return addLogoWithImagick($image_path, $logo_path);
            }
            
            $image_info = getimagesize($image_path);
            if ($image_info === false) {
                return ['success' => false, 'error' => 'Invalid image'];
            }
            
            $img = null;
            switch ($image_info[2]) {
                case IMAGETYPE_JPEG:
                    $img = @imagecreatefromjpeg($image_path);
                    break;
                case IMAGETYPE_PNG:
                    $img = @imagecreatefrompng($image_path);
                    break;
                case IMAGETYPE_BMP:
                    $img = @imagecreatefrombmp($image_path);
                    break;
            }
            
            if ($img === false || $img === null) {
                return ['success' => false, 'error' => 'Cannot create image resource'];
            }
            
            // แก้ปัญหาการหมุนรูปตาม EXIF Orientation
            $original_orientation = 1;
            if ($image_info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
                $exif = @exif_read_data($image_path);
                if ($exif && isset($exif['Orientation'])) {
                    $original_orientation = $exif['Orientation'];
                    switch ($exif['Orientation']) {
                        case 3:
                            $img = imagerotate($img, 180, 0);
                            break;
                        case 6:
                            $img = imagerotate($img, -90, 0);
                            break;
                        case 8:
                            $img = imagerotate($img, 90, 0);
                            break;
                    }
                }
            }
            
            $logo_info = getimagesize($logo_path);
            if ($logo_info === false) {
                imagedestroy($img);
                return ['success' => false, 'error' => 'Invalid logo'];
            }
            
            $logo_img = null;
            switch ($logo_info[2]) {
                case IMAGETYPE_JPEG:
                    $logo_img = @imagecreatefromjpeg($logo_path);
                    break;
                case IMAGETYPE_PNG:
                    $logo_img = @imagecreatefrompng($logo_path);
                    break;
                case IMAGETYPE_BMP:
                    $logo_img = @imagecreatefrombmp($logo_path);
                    break;
            }
            
            if ($logo_img === false || $logo_img === null) {
                imagedestroy($img);
                return ['success' => false, 'error' => 'Cannot create logo resource'];
            }
            
            // ========================================
            // คำนวณขนาด logo ใหม่โดยไม่บิดเบี้ยว
            // ========================================
            $img_width = imagesx($img);
            $img_height = imagesy($img);
            
            $logo_orig_w = imagesx($logo_img);
            $logo_orig_h = imagesy($logo_img);

            // กำหนดขนาดเป้าหมาย = 10% ของความสูงรูป
            $target_height = $img_height * 0.1;
            
            // คำนวณ scale factor จากความสูง
            $scale = $target_height / $logo_orig_h;
            
            // ใช้ scale เดียวกันกับทั้งกว้างและสูง (นี่คือกุญแจสำคัญ!)
            $logo_new_w = round($logo_orig_w * $scale);
            $logo_new_h = round($logo_orig_h * $scale);
            
            // ========================================
            // สร้าง canvas ที่มีขนาดพอดีกับ logo ที่ scale แล้ว
            // ไม่บังคับให้เป็นสี่เหลี่ยมจัตุรัส
            // ========================================
            $logo_resized = imagecreatetruecolor($logo_new_w, $logo_new_h);
            
            // รักษา transparency
            imagealphablending($logo_resized, false);
            imagesavealpha($logo_resized, true);
            $transparent = imagecolorallocatealpha($logo_resized, 0, 0, 0, 127);
            imagefill($logo_resized, 0, 0, $transparent);
            imagealphablending($logo_resized, true);
            
            // ========================================
            // Resize logo โดยใช้ scale factor เดียวกัน
            // และ canvas ที่มีขนาดพอดี ไม่บังคับให้พอดี
            // ========================================
            imagecopyresampled(
                $logo_resized,           // destination (ขนาดพอดีกับ logo)
                $logo_img,               // source
                0, 0,                    // dest x, y (เริ่มที่ 0,0)
                0, 0,                    // src x, y (เริ่มที่ 0,0)
                $logo_new_w,             // dest width (ขนาดจริงหลัง scale)
                $logo_new_h,             // dest height (ขนาดจริงหลัง scale)
                $logo_orig_w,            // src width (ขนาดเดิม)
                $logo_orig_h             // src height (ขนาดเดิม)
            );
            
            // วาง logo ที่มุมขวาบน
            $margin = max(10, (int)($img_width * 0.01));
            $pos_x = $img_width - $logo_new_w - $margin;
            $pos_y = $margin;
            
            // วาง logo ลงบนรูป
            imagealphablending($img, true);
            imagesavealpha($img, true);
            imagecopy($img, $logo_resized, $pos_x, $pos_y, 0, 0, $logo_new_w, $logo_new_h);
            
            // บันทึกรูป
            $save_success = false;
            switch ($image_info[2]) {
                case IMAGETYPE_JPEG:
                    $save_success = imagejpeg($img, $image_path, 95);
                    break;
                case IMAGETYPE_PNG:
                    $save_success = imagepng($img, $image_path, 6);
                    break;
                case IMAGETYPE_BMP:
                    $save_success = imagebmp($img, $image_path);
                    break;
            }
            
            imagedestroy($img);
            imagedestroy($logo_img);
            imagedestroy($logo_resized);
            
            if (!$save_success) {
                return ['success' => false, 'error' => 'Cannot save image with logo'];
            }
            
            // รีเซ็ต EXIF orientation เป็น 1
            if ($image_info[2] === IMAGETYPE_JPEG && $original_orientation != 1) {
                $exiftool_path = @shell_exec('which exiftool 2>/dev/null');
                if (!empty($exiftool_path)) {
                    $escaped_path = escapeshellarg($image_path);
                    @shell_exec("exiftool -Orientation=1 -n -overwrite_original {$escaped_path} 2>/dev/null");
                }
            }
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    function addLogoWithImagick($image_path, $logo_path) {
        try {
            $image = new Imagick($image_path);
            $logo = new Imagick($logo_path);
            
            $image->autoOrientImage();
            
            $img_width = $image->getImageWidth();
            $img_height = $image->getImageHeight();
            
            // ========================================
            // คำนวณขนาด logo ใหม่โดยไม่บิดเบี้ยวด้วย Imagick
            // ========================================
            $logo_orig_w = $logo->getImageWidth();
            $logo_orig_h = $logo->getImageHeight();
            
            // กำหนดขนาดเป้าหมาย = 10% ของความสูงรูป
            $target_height = $img_height * 0.1;
            
            // คำนวณ scale factor จากความสูง
            $scale = $target_height / $logo_orig_h;
            
            // ใช้ scale เดียวกันกับทั้งกว้างและสูง
            $logo_new_w = round($logo_orig_w * $scale);
            $logo_new_h = round($logo_orig_h * $scale);
            
            // Resize โดยรักษา aspect ratio
            $logo->resizeImage($logo_new_w, $logo_new_h, Imagick::FILTER_LANCZOS, 1);
            
            // วาง logo ที่มุมขวาบน
            $margin = max(10, intval($img_width * 0.01));
            $pos_x = $img_width - $logo_new_w - $margin;
            $pos_y = $margin;
            
            $image->compositeImage($logo, Imagick::COMPOSITE_OVER, $pos_x, $pos_y);
            
            $image->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
            $image->setImageCompressionQuality(95);
            $image->writeImage($image_path);
            
            $image->clear();
            $logo->clear();
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Imagick error: ' . $e->getMessage()];
        }
    }
?>