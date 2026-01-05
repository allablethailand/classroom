$(document).ready(function() {
    classroom_id = $("#classroom_id").val();
    // console.log("Classroom ID:", classroom_id);
});
const MAX_FILE_SIZE = 100 * 1024 * 1024;
const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'bmp'];
const MAX_FILES = 1000;

let selectedFiles = [];

function buildPhotoPage() {

    $(".content-container").html(`
        <div class="text-right" style="margin-bottom: 15px;">
            <button type="button" class="btn btn-purple scan-duplicates" style="margin-right:5px;">
                <i class="fas fa-search"></i> <span lang="en">Scan for Duplicates</span>
            </button>
            <button type="button" class="hidden btn btn-success sync-default-albums" style="margin-right:5px;">
                <i class="fas fa-sync"></i> <span lang="en">Refresh Default Albums</span>
            </button>
            <button type="button" class="btn btn-green manage-album" album_id=""><i class="fas fa-plus"></i> <span lang="en">New Album</span></button> 
            
        </div>
        <div class="alert alert-info" style="margin-bottom:15px;">
            <i class="fas fa-info-circle"></i> <strong>Virtual Default Albums:</strong> 
            The first 5 albums (<strong>Public</strong>, <strong>Report</strong>, <strong>Restrict</strong>, <strong>Delete</strong>, <strong>Duplicates</strong>) automatically show images based on their status. 
            Images are stored in user albums but appear in default albums when they match the criteria.
        </div>
        <div class="album-container"></div>  
    `);

    
    buildAlbum();
    // <a href="/events/gallery/?${classroom_id}" class="btn btn-info" target="_blank"><i class="fas fa-share-alt"></i> <span lang="en">Gallery</span></a> 
}

// ============ Scan for Duplicates ============
$(document).on("click", ".scan-duplicates", function() {
    swal({
        title: "Scan for Duplicate Groups",
        text: "This will analyze face embeddings to find groups of duplicate photos. This may take a few minutes.",
        type: "info",
        showCancelButton: true,
        confirmButtonText: 'Start Scan',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#6f42c1',
        closeOnConfirm: false,
        showLoaderOnConfirm: true
    }, function(isConfirm) {
        if (isConfirm) {
            $.ajax({
                url: "/classroom/management/actions/photo.php",
                type: "POST",
                data: {
                    action: 'scanDuplicates',
                    classroom_id: classroom_id
                },
                dataType: "JSON",
                timeout: 300000, // 5 minutes
                success: function(result) {
                    if (!result.status) {
                        swal({
                            type: 'error',
                            title: "Scan Failed",
                            text: result.message || "Cannot scan for duplicates",
                            timer: 3000
                        });
                        return;
                    }
                    
                    swal({
                        type: 'success',
                        title: "Scan Complete",
                        html: true,
                        text: `<div style="font-size:15px;">
                                   Found <strong>${result.duplicate_groups}</strong> duplicate group(s)!<br>
                                   <small style="color:#6f42c1;">Total ${result.total_photos_in_groups} photos in groups</small><br>
                                   <small style="color:#999;">Check the "Duplicates" album to review them.</small>
                               </div>`,
                        confirmButtonText: 'OK'
                    });
                    buildAlbum();
                },
                error: function(xhr, status, error) {
                    swal({
                        type: 'error',
                        title: "Scan Error",
                        text: "Failed: " + (error || "Unknown error"),
                        timer: 3000
                    });
                }
            });
        }
    });
});

// Event Handler: Click Virtual Album Card = View Album
$(document).on("click", ".virtual-album-card", function(e) {
    if ($(e.target).closest('.view-album').length > 0) {
        return;
    }
    let album_id = $(this).find('.view-album').attr('album_id');
    if (album_id) {
        $(this).find('.view-album').trigger('click');
    }
});

$(document).on("mousedown", ".virtual-album-card", function() {
    $(this).find('.virtual-card-hover').css({
        'transform': 'translateY(-2px) scale(0.98)',
        'transition': 'all 0.1s ease'
    });
});

$(document).on("mouseup mouseleave", ".virtual-album-card", function() {
    $(this).find('.virtual-card-hover').css({
        'transform': '',
        'transition': 'all 0.3s ease'
    });
});

$(document).on("click", ".sync-default-albums", function() {
    swal({
        title: "Refresh Albums",
        text: "This will refresh the album list and ensure default albums exist.",
        type: "info",
        showCancelButton: true,
        confirmButtonText: 'Refresh',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745',
        closeOnConfirm: false,
        showLoaderOnConfirm: true
    }, function(isConfirm) {
        if (isConfirm) {
            $.ajax({
                url: "/classroom/management/actions/photo.php",
                type: "POST",
                data: {
                    action: 'syncDefaultAlbums',
                    classroom_id: classroom_id
                },
                dataType: "JSON",
                success: function(result) {
                    swal({
                        type: 'success',
                        title: "Refreshed",
                        text: result.message || "Albums refreshed successfully",
                        timer: 2000,
                        showConfirmButton: false
                    });
                    buildAlbum();
                }
            });
        }
    });
});

$(document).on("click", ".manage-album", function() {
    let album_id = ($(this).attr("album_id")) ? $(this).attr("album_id") : '';
    $(".systemModal").modal();
    $(".systemModal .modal-header").html(`
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h5 class="modal-title">${(album_id) ? '<span lang="en">Manage Album</span>' : '<span lang="en">New Album</span>'}</h5>
    `);
    $(".systemModal .modal-footer").html(`
        <button type="button" class="btn btn-orange save-album" lang="en">Save</button>
        <button type="button" class="btn btn-white" data-dismiss="modal" lang="en">Close</button>
    `);
    $(".systemModal .modal-body").html(`
        <form id="album_form">
            <input type="hidden" name="album_id" value="${album_id}">
            <p style="margin: 10px auto;"><span lang="en">Album Name</span> <code>*</code></p>
            <input type="text" class="form-control object-require" id="album_name" name="album_name">
            <p style="color:#999; font-size:12px; margin:5px 0;">Note: Cannot use reserved names (Public, Report, Delete, Restrict, Duplicates)</p>
            <p style="margin: 10px auto;"><span lang="en">Description</span></p>
            <textarea class="form-control" id="album_description" name="album_description"></textarea>
        </form>
    `);
    if(album_id) {
        $.ajax({
            url: "/classroom/management/actions/photo.php",
            type: "POST",
            data: {
                action:'albumData',
                album_id: album_id
            },
            dataType: "JSON",
            success: function(result){
                let album_data = result.album_data;
                $("#album_name").val(album_data.album_name);
                $("#album_description").val(album_data.album_description);
            }
        });
    }
});

$(document).on("click", ".delete-album", function() {
    let album_id = $(this).attr("album_id");
    swal({
        html: true,
        title: window.lang.translate("Are you sure?"),
        text: 'You want to delete this Album?',
        type: "warning",
        showCancelButton: true,
        closeOnConfirm: false,
        confirmButtonText: window.lang.translate('Yes'),
        cancelButtonText: window.lang.translate("Cancel"),	
        confirmButtonColor: '#FF9900',
        cancelButtonColor: '#CCCCCC',
        showLoaderOnConfirm: true,
    },
    function(isConfirm){
        if (isConfirm) {
           $.ajax({
                url: "/classroom/management/actions/photo.php",
                method: "POST",
                dataType: "json",
                data: {
                    action: "deleteAlbum",
                    album_id: album_id
                },
                success: function (res) {
                    if (!res.status) {
                        swal({
                            type: 'error',
                            title: "Error",
                            text: res.message || "Cannot delete this album",
                            timer: 3000
                        });
                        return;
                    }
                    swal({
                        type: 'success',
                        title: "Successfully",
                        text: "",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    buildAlbum();
                }
            });
        } else {
            swal.close();
        }
    });
});

$(document).on("click", ".save-album", function() {
    var err = 0;
	$.each($(".object-require"), function(){    
		if(!$(this).val()) {
			++err;
		}              
	});
	if(err > 0) {
		swal({
			type: 'warning',
			title: "Warning",
			text: "Please enter album name.",
			timer: 5000,
			showConfirmButton: false,
			allowOutsideClick: true
		});
		return;
	}
    $(".loader").addClass("active");
    var fd = new FormData(document.getElementById("album_form"));
    fd.append("classroom_id", classroom_id);
	$.ajax({
		url: "/classroom/management/actions/photo.php?action=saveAlbum",
		type: "POST",
		data: fd,
		processData: false,
		contentType: false,
		dataType: "JSON",
		success: function(result){
			$(".loader").removeClass("active");
            if (!result.status) {
                swal({
                    type: 'error',
                    title: "Error",
                    text: result.message || "Cannot save album",
                    timer: 3000
                });
                return;
            }
			swal({type: 'success', title: "Successfully", text: "", showConfirmButton: false, timer: 1500});
			$(".systemModal").modal("hide");
            buildAlbum();
		}
	});
});

function buildAlbum() {
    $(".loader").addClass("active");
    $.ajax({
        url: "/classroom/management/actions/photo.php",
        method: "POST",
        dataType: "json",
        data: {
            action: "buildAlbum",
            classroom_id: classroom_id
        },
        success: function (res) {
            $(".loader").removeClass("active");
            let album_data = res.album_data;
            if(!album_data || album_data.length === 0) {
                $(".album-container").html(`
                    <h1 class="text-grey text-center"><i class="fas fa-images fa-2x"></i></h1>    
                    <p class="text-grey text-center" lang="en">No Album</p>    
                `);
                return;
            }
            buildAlbumDisplay(album_data);
        }
    });
}

function buildAlbumDisplay(album_data) {
    let virtualAlbums = album_data.filter(a => a.is_default);
    let userAlbums = album_data.filter(a => !a.is_default);
    
    let html = '';
    
    if (virtualAlbums.length > 0) {
        html += `
        <div style="margin-bottom:30px;">
            <div style="background:linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); padding:25px 15px; border-radius:0 0 12px 12px; box-shadow:0 4px 15px rgba(0,0,0,0.1);">
                <div class="row">
        `;
        
        $.each(virtualAlbums, function(i, item){
            let coverImage = item.cover_image || '';
            
            let albumStyle = {};
            switch(item.album_name) {
                case 'Public':
                    albumStyle = {
                        gradient: 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
                        shadowColor: 'rgba(56, 239, 125, 0.4)',
                        icon: 'fa-globe-americas',
                        badgeColor: '#28a745',
                        badgeIcon: 'fa-check-circle'
                    };
                    break;
                case 'Report':
                    albumStyle = {
                        gradient: 'linear-gradient(135deg, #ee0979 0%, #ff6a00 100%)',
                        shadowColor: 'rgba(238, 9, 121, 0.4)',
                        icon: 'fa-flag',
                        badgeColor: '#dc3545',
                        badgeIcon: 'fa-exclamation-triangle'
                    };
                    break;
                case 'Restrict':
                    albumStyle = {
                        gradient: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                        shadowColor: 'rgba(240, 147, 251, 0.4)',
                        icon: 'fa-user-lock',
                        badgeColor: '#e83e8c',
                        badgeIcon: 'fa-lock'
                    };
                    break;
                case 'Delete':
                    albumStyle = {
                        gradient: 'linear-gradient(135deg, #4b6cb7 0%, #182848 100%)',
                        shadowColor: 'rgba(75, 108, 183, 0.4)',
                        icon: 'fa-trash-alt',
                        badgeColor: '#6c757d',
                        badgeIcon: 'fa-times-circle'
                    };
                    break;
                case 'Duplicates':
                    albumStyle = {
                        gradient: 'linear-gradient(135deg, #6a11cb 0%, #2575fc 100%)',
                        shadowColor: 'rgba(106, 17, 203, 0.4)',
                        icon: 'fa-clone',
                        badgeColor: '#6f42c1',
                        badgeIcon: 'fa-search'
                    };
                    break;
            }
            
            html += `
            <div class="col-sm-6 col-md-3" style="margin-bottom:20px;">
                <div class="virtual-album-card" style="position:relative; cursor:pointer; transition:all 0.3s ease;">
                    <div style="position:relative; height:240px; background:white; border-radius:15px; overflow:hidden; 
                                box-shadow:0 8px 20px ${albumStyle.shadowColor}; transition:all 0.3s ease;"
                         class="virtual-card-hover">
                        
                        <div style="background:${albumStyle.gradient}; padding:15px; position:relative; overflow:hidden;">
                            <div style="position:absolute; top:-20px; right:-20px; font-size:80px; opacity:0.15;">
                                <i class="fas ${albumStyle.icon}"></i>
                            </div>
                            <div style="position:relative; z-index:1;">
                                <h5 style="margin:0; color:white; font-weight:700; font-size:18px;">
                                    <i class="fas ${albumStyle.icon}" style="margin-right:8px;"></i>
                                    ${item.album_name}
                                </h5>
                                <p style="margin:5px 0 0 0; color:rgba(255,255,255,0.95); font-size:12px; line-height:1.4;">
                                    ${item.album_description}
                                </p>
                            </div>
                        </div>
                        
                        ${coverImage ? `
                            <div style="height:120px; background:url('${coverImage}') center/contain no-repeat; position:relative;">
                                <div style="position:absolute; bottom:0; left:0; right:0; 
                                           background:linear-gradient(to top, rgba(0,0,0,0.8), transparent); 
                                           padding:15px 10px 10px;">
                                    <div style="background:rgba(255,255,255,0.95); padding:5px 12px; border-radius:20px; display:inline-block;">
                                        <i class="fas ${item.album_name === 'Duplicates' ? 'fa-images' : 'fa-images'}" style="color:${albumStyle.badgeColor}; margin-right:5px;"></i>
                                        <span style="color:#333; font-weight:700; font-size:14px;">${item.image_count}</span>
                                        <span style="color:#666; font-size:12px;"> ${item.album_name === 'Duplicates' ? 'Pairs' : 'Photos'}</span>
                                    </div>
                                </div>
                            </div>
                        ` : `
                            <div style="height:120px; background:${albumStyle.gradient}; opacity:0.15; 
                                       display:flex; align-items:center; justify-content:center; position:relative;">
                                <i class="fas ${albumStyle.icon}" style="font-size:60px; color:rgba(255,255,255,0.3);"></i>
                            </div>
                            <div style="position:absolute; top:85px; left:50%; transform:translateX(-50%); width:80%;">
                                <div style="background:rgba(255,255,255,0.98); padding:8px 15px; border-radius:25px; 
                                           text-align:center; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                                    <i class="fas ${item.album_name === 'Duplicates' ? 'fa-clone' : 'fa-images'}" style="color:${albumStyle.badgeColor}; margin-right:5px;"></i>
                                    <span style="color:#333; font-weight:700; font-size:16px;">${item.image_count}</span>
                                    <span style="color:#666; font-size:13px;"> ${item.album_name === 'Duplicates' ? 'Pairs' : 'Photos'}</span>
                                </div>
                            </div>
                        `}
                        
                        <div style="padding:12px 15px; background:white;">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="display:inline-block; background:${albumStyle.badgeColor}; color:white; 
                                           padding:4px 10px; border-radius:12px; font-size:11px; font-weight:600;">
                                    <i class="fas ${albumStyle.badgeIcon}"></i> VIRTUAL
                                </span>
                                <small style="color:#999; font-size:11px;">
                                    <i class="far fa-clock"></i> ${item.date_modify}
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-top:12px; text-align:center;">
                        <button class="btn btn-primary btn-block view-album" album_id="${item.album_id}" 
                                style="border-radius:25px; padding:10px; font-weight:600; 
                                       background:${albumStyle.gradient}; border:none;
                                       box-shadow:0 4px 12px ${albumStyle.shadowColor};">
                            <i class="fas fa-images"></i> ${item.album_name === 'Duplicates' ? 'Review Pairs' : 'View Album'}
                        </button>
                    </div>
                </div>
            </div>`;
        });
        
        html += `
                </div>
            </div>
        </div>
        `;
    }
    
    if (userAlbums.length > 0) {
        html += `
        <div style="margin-top:40px;">
            <div style="background:linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%); padding:12px 20px; border-radius:8px; margin-bottom:20px; box-shadow:0 4px 12px rgba(33,147,176,0.3);">
                <h4 style="margin:0; color:white; font-weight:600;">
                    <i class="fas fa-folder-open" style="margin-right:5px;"></i>
                    Albums (${userAlbums.length})
                </h4>
            </div>
            <div class="row">
        `;
        
        $.each(userAlbums, function(i, item){
            let coverImage = item.cover_image || '';
            
            html += `
            <div class="col-sm-6 col-md-4 col-lg-3" style="margin-bottom:30px;">
                <div class="album-card" style="position:relative; cursor:pointer; transition:transform 0.3s ease;">
                    <div class="album-stack" style="position:relative; height:220px;">
                        <div style="position:absolute; top:8px; left:8px; right:-8px; bottom:-8px; background:white; border:1px solid #e0e0e0; border-radius:12px; transform:rotate(2deg); box-shadow:0 2px 8px rgba(0,0,0,0.08);"></div>
                        <div style="position:absolute; top:4px; left:4px; right:-4px; bottom:-4px; background:white; border:1px solid #e0e0e0; border-radius:12px; transform:rotate(1deg); box-shadow:0 2px 8px rgba(0,0,0,0.08);"></div>
                        
                        <div class="album-main-card" style="position:relative; height:100%; background:white; border:1px solid #e0e0e0; border-radius:12px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.1); transition:all 0.3s ease;">
                            ${coverImage ? `
                                <div style="height:160px; background:url('${coverImage}') center/contain no-repeat; position:relative;">
                                    <div style="position:absolute; bottom:0; left:0; right:0; background:linear-gradient(to top, rgba(0,0,0,0.7), transparent); padding:15px 10px 10px;">
                                        <div style="background:rgba(255,255,255,0.95); padding:3px 10px; border-radius:15px; display:inline-block;">
                                            <i class="fas fa-images" style="color:#667eea; margin-right:5px;"></i>
                                            <span style="color:#333; font-weight:600; font-size:13px;">${item.image_count} Photos</span>
                                        </div>
                                    </div>
                                </div>
                            ` : `
                                <div style="height:160px; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); display:flex; align-items:center; justify-content:center; flex-direction:column;">
                                    <i class="fas fa-folder-open" style="font-size:48px; color:rgba(255,255,255,0.9); margin-bottom:10px;"></i>
                                    <div style="background:rgba(255,255,255,0.95); padding:3px 10px; border-radius:15px; display:inline-block;">
                                        <i class="fas fa-images" style="color:#667eea; margin-right:5px;"></i>
                                        <span style="color:#333; font-weight:600; font-size:13px;">${item.image_count} Photos</span>
                                    </div>
                                </div>
                            `}
                            
                            <div style="padding:12px 15px; background:white;">
                                <h5 style="margin:0 0 5px 0; font-size:16px; font-weight:600; color:#333; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="${item.album_name}">
                                    ${item.album_name}
                                </h5>
                                ${item.album_description ? `
                                    <p style="margin:0 0 5px 0; font-size:12px; color:#666; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="${item.album_description}">
                                        ${item.album_description}
                                    </p>
                                ` : ''}
                                <small style="color:#999; font-size:11px;">
                                    <i class="far fa-clock" style="margin-right:3px;"></i>${item.date_modify}
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="album-actions" style="margin-top:12px; text-align:center;">
                        <div class="btn-group" role="group">
                            <button class="btn btn-primary btn-sm view-album" album_id="${item.album_id}" style="border-radius:20px 0 0 20px; padding:6px 15px;">
                                <i class="fas fa-images"></i> <span class="hidden-xs">View</span>
                            </button>
                            <button class="btn btn-warning btn-sm manage-album" album_id="${item.album_id}" style="padding:6px 15px;">
                                <i class="fa fa-edit"></i> <span class="hidden-xs">Edit</span>
                            </button>
                            <button class="btn btn-danger btn-sm delete-album" album_id="${item.album_id}" style="border-radius:0 20px 20px 0; padding:6px 15px;">
                                <i class="fa fa-trash"></i> <span class="hidden-xs">Delete</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;
        });
        
        html += `
            </div>
        </div>
        `;
    }
    
    if (!$('#album-hover-style').length) {
        $('<style id="album-hover-style">')
            .text(`
                .album-card:hover {
                    transform: translateY(-5px);
                }
                .album-card:hover .album-main-card {
                    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
                }
                .album-card:active {
                    transform: translateY(-2px);
                }
                
                .virtual-album-card:hover .virtual-card-hover {
                    transform: translateY(-5px);
                    box-shadow: 0 12px 30px rgba(0,0,0,0.2);
                }
                .virtual-album-card:active .virtual-card-hover {
                    transform: translateY(-2px);
                }
                
                .btn-purple {
                    background-color: #6f42c1;
                    border-color: #6f42c1;
                    color: #fff;
                }
                .btn-purple:hover {
                    background-color: #5a32a3;
                    border-color: #5a32a3;
                    color: #fff;
                }
            `)
            .appendTo('head');
    }
    
    $(".album-container").html(html);
}

$(document).on("click", ".album-card", function(e) {
    if ($(e.target).closest('.album-actions').length > 0) {
        return;
    }
    let album_id = $(this).find('.view-album').attr('album_id');
    if (album_id) {
        $(this).find('.view-album').trigger('click');
    }
});

// Global variables for image management
let currentAlbumImages = [];
let selectedImageIds = [];
let currentStatusFilter = 'all';
let currentVisibilityFilter = 'all';
let currentAlbumId = null;
let currentAlbumInfo = null;

$(document).on("click", ".view-album", function () {
    let album_id = $(this).attr("album_id");
    currentAlbumId = album_id;
    
    $.ajax({
        url: "/classroom/management/actions/photo.php",
        method: "POST",
        dataType: "json",
        data: {
            action: "buildAlbum",
            classroom_id: classroom_id
        },
        success: function (res) {
            let album_info = res.album_data.find(a => a.album_id == album_id);
            currentAlbumInfo = album_info;
            
            let isDefault = album_info ? album_info.is_default : false;
            let albumName = album_info ? album_info.album_name : '';
            
            // ===== ถ้าเป็น Duplicates Album ให้แสดง UI พิเศษ =====
            if (albumName === 'Duplicates') {
                showDuplicatesAlbum(album_id);
                return;
            }
            
            $(".loader").addClass("active");
            $(".systemModal").modal();
            $(".systemModal .modal-dialog").css({"max-width": "95%", "width": "95%"});
            $(".systemModal .modal-header").html(`
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h5 class="modal-title">
                    <span lang="en">Manage Images</span> - ${albumName}
                    ${isDefault ? '<span class="badge badge-info" style="margin-left:10px;">Virtual Album (Status Filter)</span>' : ''}
                </h5>
            `);
            $(".systemModal .modal-footer").html(`
                <button type="button" class="btn btn-white" data-dismiss="modal" lang="en">Close</button>
            `);
            
            let uploadSection = '';
            
            if (!isDefault) {
                uploadSection = `
                    <div class="upload-section text-center"
                        style="border:2px dashed #ccc; padding:40px 30px; border-radius:8px; cursor:pointer; background:#fafafa; transition:all 0.3s;"
                        onmouseover="this.style.borderColor='#667eea'; this.style.background='#f0f4ff'"
                        onmouseout="this.style.borderColor='#ccc'; this.style.background='#fafafa'">
                        <i class="fas fa-cloud-upload-alt" style="font-size:48px; color:#667eea; margin-bottom:15px;"></i>
                        <h4 style="margin:10px 0; color:#333;">Drag & Drop Images Here</h4>
                        <p style="color:#999; margin:5px 0;">(or click to select files, max 1000 files)</p>
                        <p style="color:#999; margin:5px 0; font-size:12px;">Allowed: JPG, JPEG, PNG, BMP (Max 100MB per file)</p>
                        <p style="color:#28a745; margin:5px 0; font-size:11px; font-weight:600;">
                            <i class="fas fa-info-circle"></i> Images will be automatically resized in background queue
                        </p>
                        <input type="file" id="uploadInput" multiple style="display:none;" accept="image/jpeg,image/jpg,image/png,image/bmp">
                        <button class="btn btn-primary btn-upload-select" style="margin-top:15px; padding:10px 30px; border-radius:25px;">
                            <i class="fas fa-file-image"></i> Select Files
                        </button>
                        <div class="pending-files" style="margin-top:20px; display:none;">
                            <div style="background:#fff; border:1px solid #e0e0e0; border-radius:8px; padding:15px;">
                                <h5 style="margin:0 0 10px 0; color:#333;"><i class="fas fa-list"></i> Files Selected:</h5>
                                <ul class="file-list" style="text-align:left; max-height:150px; overflow-y:auto; margin:10px 0; padding-left:20px; list-style:none;">
                                </ul>
                                <button class="btn btn-success btn-upload-start" style="margin-top:10px; padding:10px 30px; border-radius:25px;" album_id="${album_id}">
                                    <i class="fas fa-upload"></i> Upload (<span class="file-count">0</span>) Files
                                </button>
                                <div class="upload-status text-info" style="margin-top:15px; display:none; font-weight:600;"></div>
                            </div>
                        </div>
                    </div>
                    <hr>
                `;
            } else {
                uploadSection = `
                    <div class="alert" style="background:linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-left:5px solid #2196f3; border-radius:8px; padding:20px; margin-bottom:20px;">
                        <h5 style="margin:0 0 10px 0; color:#1565c0; font-weight:700;">
                            <i class="fas fa-magic" style="margin-right:8px;"></i>
                            Virtual Album Information
                        </h5>
                        <p style="margin:0 0 10px 0; color:#0d47a1; line-height:1.6;">
                            This is a <strong>virtual album</strong> that automatically displays images based on their status from all albums. 
                            You cannot upload directly to this album.
                        </p>
                        <div style="background:rgba(255,255,255,0.8); padding:12px; border-radius:6px; margin-top:12px;">
                            <strong style="color:#1976d2;"><i class="fa fa-lightbulb"></i> To add images:</strong>
                            <ol style="margin:8px 0 0 20px; padding:0; color:#424242; line-height:1.8;">
                                <li>Upload to a <strong>User Album</strong></li>
                                <li>Images will appear here automatically based on their status</li>
                            </ol>
                        </div>
                    </div>
                `;
            }
            
            $(".systemModal .modal-body").html(`
                ${uploadSection}
                
                <div class="image-filter-bar" style="background:#f8f9fa; padding:15px; border-radius:8px; margin-bottom:15px;">
                    <div class="row">
                        <div class="col-md-12" style="margin-bottom:10px;">
                            <label style="font-weight:600; margin-right:10px; color:#555;">
                                <i class="fas fa-filter"></i> Filter by Status:
                            </label>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-default filter-status active" data-status="all">
                                    <i class="fas fa-th"></i> All (<span class="count-all">0</span>)
                                </button>
                                <button class="btn btn-sm btn-default filter-status" data-status="completed">
                                    <i class="fas fa-check-circle text-success"></i> Completed (<span class="count-completed">0</span>)
                                </button>
                                <button class="btn btn-sm btn-default filter-status" data-status="processing">
                                    <i class="fas fa-spinner text-info"></i> Processing (<span class="count-processing">0</span>)
                                </button>
                                <button class="btn btn-sm btn-default filter-status" data-status="pending">
                                    <i class="fas fa-clock text-warning"></i> Pending (<span class="count-pending">0</span>)
                                </button>
                                <button class="btn btn-sm btn-default filter-status" data-status="error">
                                    <i class="fas fa-exclamation-triangle text-danger"></i> Error (<span class="count-error">0</span>)
                                </button>
                            </div>
                        </div>
                        <div class="col-md-12" style="margin-bottom:10px;">
                            <label style="font-weight:600; margin-right:10px; color:#555;">
                                <i class="fas fa-eye"></i> Filter by Visibility:
                            </label>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-default filter-visibility active" data-visibility="all">
                                    <i class="fas fa-th"></i> All (<span class="count-vis-all">0</span>)
                                </button>
                                <button class="btn btn-sm btn-default filter-visibility" data-visibility="public">
                                    <i class="fas fa-globe text-success"></i> Public (<span class="count-vis-public">0</span>)
                                </button>
                                <button class="btn btn-sm btn-default filter-visibility" data-visibility="private">
                                    <i class="fas fa-lock text-warning"></i> Private (<span class="count-vis-private">0</span>)
                                </button>
                            </div>
                        </div>
                        <div class="col-md-12 text-right">
                            <button class="btn btn-sm btn-info" id="selectAllImages" style="margin-right:5px;">
                                <i class="fas fa-check-square"></i> Select All (<span class="selected-count">0</span>)
                            </button>
                            <button class="btn btn-sm btn-primary" id="moveSelected" disabled>
                                <i class="fas fa-folder"></i> Move Selected
                            </button>
                            <button class="btn btn-sm btn-success" id="downloadSelected" disabled>
                                <i class="fas fa-download"></i> Download Selected
                            </button>
                            <button class="btn btn-sm btn-danger" id="deleteSelected" disabled>
                                <i class="fas fa-trash"></i> Delete Selected
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="image-table-container"></div>
            `);
            loadImageList(album_id);
        }
    });
});

// ============ Duplicates Album UI ============
function showDuplicatesAlbum(album_id) {
    $(".loader").addClass("active");
    $(".systemModal").modal();
    $(".systemModal .modal-dialog").css({"max-width": "95%", "width": "95%"});
    $(".systemModal .modal-header").html(`
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h5 class="modal-title">
            <i class="fas fa-clone text-purple"></i> Duplicate Photo Groups
            <span class="badge badge-purple" style="margin-left:10px; background:#6f42c1;">Virtual Album</span>
        </h5>
    `);
    $(".systemModal .modal-footer").html(`
        <button type="button" class="btn btn-purple scan-duplicates-inline" style="margin-right:auto;">
            <i class="fas fa-sync"></i> Rescan
        </button>
        <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
    `);
    
    $(".systemModal .modal-body").html(`
        <div class="alert" style="background:linear-gradient(135deg, #f3e7ff 0%, #e8d5ff 100%); border-left:5px solid #6f42c1; border-radius:8px; padding:20px; margin-bottom:20px;">
            <h5 style="margin:0 0 10px 0; color:#5a32a3; font-weight:700;">
                <i class="fas fa-info-circle" style="margin-right:8px;"></i>
                About Duplicate Groups (V2)
            </h5>
            <p style="margin:0 0 10px 0; color:#4a2889; line-height:1.6;">
                This system uses <strong>clustering algorithm</strong> to group similar photos together. 
                Instead of showing pairs, it shows <strong>complete groups</strong> of duplicate photos.
            </p>
            <div style="background:rgba(255,255,255,0.8); padding:12px; border-radius:6px; margin-top:12px;">
                <strong style="color:#6f42c1;"><i class="fa fa-lightbulb"></i> Features:</strong>
                <ul style="margin:8px 0 0 20px; padding:0; color:#424242; line-height:1.8;">
                    <li><strong>Multiple photos per group</strong> (2-10+ photos)</li>
                    <li><strong>Select & delete</strong> specific photos</li>
                    <li><strong>Keep one & delete rest</strong> in one click</li>
                    <li><strong>Mark entire group</strong> as not duplicate</li>
                </ul>
            </div>
        </div>
        
        <div class="duplicate-groups-container">
            <div class="text-center" style="padding:40px;">
                <i class="fas fa-spinner fa-spin fa-3x text-purple"></i>
                <p style="margin-top:15px; color:#6c757d;">Loading duplicate groups...</p>
            </div>
        </div>
    `);
    
    loadDuplicateGroups();
}

// ============================================================
// Load Duplicate Groups (NEW - NOT PAIRS!)
// ============================================================
function loadDuplicateGroups() {
    $.ajax({
        url: "/classroom/management/actions/photo.php",
        method: "POST",
        dataType: "json",
        data: {
            action: "getDuplicateGroups",  // ← NEW ACTION
            classroom_id: classroom_id
        },
        success: function(res) {
            $(".loader").removeClass("active");
            
            if (!res.status) {
                $('.duplicate-groups-container').html(`
                    <div class="text-center" style="padding:40px;">
                        <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                        <p style="margin-top:15px; color:#dc3545;">Failed to load duplicate groups</p>
                        <p style="color:#999;">${res.message || 'Unknown error'}</p>
                    </div>
                `);
                return;
            }
            
            if (!res.groups || res.groups.length === 0) {
                $('.duplicate-groups-container').html(`
                    <div class="text-center" style="padding:60px 20px;">
                        <i class="fas fa-check-circle fa-4x" style="color:#28a745; margin-bottom:20px;"></i>
                        <h4 style="color:#28a745; margin:0 0 10px 0;">No Duplicate Groups Found!</h4>
                        <p style="color:#6c757d;">All photos appear to be unique. Run a scan if you've added new images.</p>
                        <button class="btn btn-purple scan-duplicates-inline" style="margin-top:20px;">
                            <i class="fas fa-search"></i> Run Scan Now
                        </button>
                    </div>
                `);
                return;
            }
            
            renderDuplicateGroups(res.groups);
        },
        error: function(xhr, status, error) {
            $(".loader").removeClass("active");
            $('.duplicate-groups-container').html(`
                <div class="text-center" style="padding:40px;">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                    <p style="margin-top:15px; color:#dc3545;">Failed to load duplicate groups</p>
                    <p style="color:#999; font-size:12px;">Error: ${error || status || 'Unknown'}</p>
                    <button class="btn btn-info" onclick="loadDuplicateGroups()" style="margin-top:15px;">
                        <i class="fas fa-redo"></i> Retry
                    </button>
                </div>
            `);
        }
    });
}

function renderDuplicateGroups(groups) {
    let html = `
        <div style="margin-bottom:20px; padding:15px; background:#f8f9fa; border-radius:8px;">
            <h5 style="margin:0; color:#333;">
                <i class="fas fa-clone text-purple"></i> 
                Found <strong>${groups.length}</strong> duplicate group(s)
            </h5>
            <p style="margin:5px 0 0 0; color:#6c757d; font-size:13px;">
                Review each group and decide which photos to keep or delete
            </p>
        </div>
        
        <div class="row">
    `;
    
    groups.forEach((group, index) => {
        html += `
        <div class="col-md-12" style="margin-bottom:30px;">
            <div class="duplicate-group-card" data-group-id="${group.group_id}" 
                 style="border:2px solid #e0e0e0; border-radius:12px; padding:20px; background:white; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                
                <!-- Header -->
                <div style="background:linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); padding:12px 20px; border-radius:8px; margin:-20px -20px 20px -20px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <h5 style="margin:0; color:white; font-weight:600;">
                            <i class="fas fa-layer-group"></i> Group #${index + 1}
                        </h5>
                        <div>
                            <span style="background:rgba(255,255,255,0.3); padding:4px 12px; border-radius:15px; margin-right:10px; color:white; font-size:13px;">
                                <i class="fas fa-images"></i> ${group.photo_count} Photos
                            </span>
                            <span style="background:rgba(255,255,255,0.3); padding:4px 12px; border-radius:15px; color:white; font-size:13px;">
                                <i class="fas fa-user"></i> ${group.face_count} Faces
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Photos Grid -->
                <div class="group-photos-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(150px, 1fr)); gap:15px; margin-bottom:20px;">
                    ${group.members.map((member, idx) => `
                        <div class="group-photo-item" style="position:relative; border:2px solid #e0e0e0; border-radius:8px; padding:8px; background:#f8f9fa; transition:all 0.3s;">
                            <div style="position:absolute; top:12px; left:12px; z-index:2;">
                                <input type="checkbox" class="photo-select-checkbox" 
                                       value="${member.photo_id}"
                                       style="width:20px; height:20px; cursor:pointer;">
                            </div>
                            <div style="position:absolute; top:12px; right:12px; z-index:2; background:rgba(0,0,0,0.7); color:white; padding:4px 8px; border-radius:12px; font-size:11px; font-weight:600;">
                                #${idx + 1}
                            </div>
                            <img src="${member.photo_thumb}" 
                                 class="photo-preview-thumb"
                                 data-full="${member.photo_full}"
                                 style="width:100%; height:150px; object-fit:cover; border-radius:6px; cursor:pointer; display:block;">
                            <div style="text-align:center; margin-top:8px;">
                                <small style="color:#6f42c1; font-weight:600;">${member.similarity}% match</small>
                            </div>
                        </div>
                    `).join('')}
                </div>
                
                <!-- Info Bar -->
                <div style="background:#f8f9fa; padding:12px; border-radius:6px; margin-bottom:15px; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <small style="color:#666;">
                            <i class="far fa-clock"></i> Detected: ${group.updated_at}
                        </small>
                    </div>
                    <div>
                        <span class="selected-count-badge" style="background:#6f42c1; color:white; padding:4px 12px; border-radius:15px; font-size:12px; font-weight:600;">
                            <i class="fas fa-check-square"></i> <span class="count">0</span> Selected
                        </span>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="btn-group btn-group-justified" style="display:flex; gap:10px;">
                    <button class="btn btn-danger delete-selected-from-group"
                            data-group-id="${group.group_id}"
                            disabled
                            style="flex:1;">
                        <i class="fa fa-trash"></i> Delete Selected
                    </button>
                    
                    <button class="btn btn-warning keep-one-delete-rest"
                            data-group-id="${group.group_id}"
                            style="flex:1;">
                        <i class="fa fa-star"></i> Keep One & Delete Rest
                    </button>
                    
                    <button class="btn btn-success mark-group-not-dup"
                            data-group-id="${group.group_id}"
                            style="flex:1;">
                        <i class="fa fa-check"></i> Not Duplicate
                    </button>
                </div>
            </div>
        </div>
        `;
    });
    
    html += `</div>`;
    
    $('.duplicate-groups-container').html(html);
    
    // Add hover effects
    if (!$('#duplicate-group-hover-style').length) {
        $('<style id="duplicate-group-hover-style">')
            .text(`
                .duplicate-group-card:hover {
                    border-color: #6f42c1;
                    box-shadow: 0 8px 20px rgba(111, 66, 193, 0.3);
                }
                .group-photo-item:hover {
                    border-color: #6f42c1;
                    transform: translateY(-5px);
                    box-shadow: 0 6px 16px rgba(0,0,0,0.2);
                }
                .photo-preview-thumb:hover {
                    opacity: 0.8;
                }
            `)
            .appendTo('head');
    }
}

// ============================================================
// Handle Checkbox Selection
// ============================================================
$(document).on('change', '.photo-select-checkbox', function() {
    let $card = $(this).closest('.duplicate-group-card');
    let selectedCount = $card.find('.photo-select-checkbox:checked').length;
    
    // Update badge
    $card.find('.selected-count-badge .count').text(selectedCount);
    
    // Enable/disable delete button
    let $deleteBtn = $card.find('.delete-selected-from-group');
    if (selectedCount > 0) {
        $deleteBtn.prop('disabled', false);
    } else {
        $deleteBtn.prop('disabled', true);
    }
});

// ============================================================
// Action: Delete Selected Photos from Group
// ============================================================
$(document).on('click', '.delete-selected-from-group', function() {
    let group_id = $(this).data('group-id');
    let $card = $(this).closest('.duplicate-group-card');
    let selected = [];
    
    $card.find('.photo-select-checkbox:checked').each(function() {
        selected.push($(this).val());
    });
    
    if (selected.length === 0) {
        swal({
            type: 'warning',
            title: "No Selection",
            text: "Please select photos to delete",
            timer: 2000
        });
        return;
    }
    
    swal({
        title: "Delete Selected Photos",
        text: `Delete ${selected.length} photo(s) from this group?`,
        type: "warning",
        showCancelButton: true,
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        closeOnConfirm: false,
        showLoaderOnConfirm: true
    }, function(isConfirm) {
        if (isConfirm) {
            let completed = 0;
            let total = selected.length;
            
            selected.forEach(photo_id => {
                $.ajax({
                    url: "/classroom/management/actions/photo.php",
                    method: "POST",
                    data: {
                        action: "deletePhotoFromGroup",
                        group_id: group_id,
                        photo_id: photo_id
                    },
                    success: function(res) {
                        completed++;
                        
                        if (completed === total) {
                            swal({
                                type: 'success',
                                title: "Deleted",
                                text: `Deleted ${total} photo(s)`,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            
                            setTimeout(() => {
                                loadDuplicateGroups();
                                buildAlbum();
                            }, 1600);
                        }
                    }
                });
            });
        }
    });
});

// ============================================================
// Action: Keep One & Delete Rest
// ============================================================
$(document).on('click', '.keep-one-delete-rest', function() {
    let group_id = $(this).data('group-id');
    let $card = $(this).closest('.duplicate-group-card');
    let photos = [];
    
    $card.find('.group-photo-item').each(function() {
        photos.push({
            photo_id: $(this).find('.photo-select-checkbox').val(),
            photo_thumb: $(this).find('.photo-preview-thumb').attr('src')
        });
    });
    
    // Build selection modal
    let modal_html = `
        <div style="padding:20px;">
            <h5 style="margin:0 0 20px 0; color:#333;">
                <i class="fa fa-star text-warning"></i> Select ONE photo to keep:
            </h5>
            <div class="keep-one-selector" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(150px, 1fr)); gap:15px;">
                ${photos.map((p, idx) => `
                    <div class="keep-one-option" style="border:2px solid #e0e0e0; border-radius:8px; padding:10px; text-align:center; cursor:pointer; transition:all 0.3s;">
                        <input type="radio" name="keep_photo" value="${p.photo_id}" 
                               id="keep_${p.photo_id}"
                               style="display:none;">
                        <label for="keep_${p.photo_id}" style="cursor:pointer; margin:0; display:block;">
                            <img src="${p.photo_thumb}" style="width:100%; height:120px; object-fit:cover; border-radius:6px; margin-bottom:8px;">
                            <small style="color:#666; font-weight:600;">Photo #${idx + 1}</small>
                        </label>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
    
    $(".systemModal").modal();
    $(".systemModal .modal-header").html(`
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h5 class="modal-title"><i class="fa fa-star"></i> Keep One Photo</h5>
    `);
    $(".systemModal .modal-body").html(modal_html);
    $(".systemModal .modal-footer").html(`
        <button class="btn btn-danger confirm-keep-one">
            <i class="fa fa-trash"></i> Delete Others
        </button>
        <button class="btn btn-default" data-dismiss="modal">Cancel</button>
    `);
    
    // Add click handler for labels
    $(document).on('click', '.keep-one-option', function() {
        $('.keep-one-option').css({
            'border-color': '#e0e0e0',
            'background': 'white'
        });
        $(this).css({
            'border-color': '#6f42c1',
            'background': '#f3e7ff'
        });
        $(this).find('input[type="radio"]').prop('checked', true);
    });
    
    // Handle confirm
    $('.confirm-keep-one').off('click').on('click', function() {
        let keep_photo_id = $('input[name="keep_photo"]:checked').val();
        
        if (!keep_photo_id) {
            swal({
                type: 'warning',
                title: "No Selection",
                text: "Please select a photo to keep",
                timer: 2000
            });
            return;
        }
        
        $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Deleting...');
        
        $.ajax({
            url: "/classroom/management/actions/photo.php",
            method: "POST",
            dataType: 'json',
            data: {
                action: "deleteGroupKeepOne",
                group_id: group_id,
                keep_photo_id: keep_photo_id
            },
            success: function(res) {
                // if (!res.status) {
                //     swal({
                //         type: 'error',
                //         title: "Error",
                //         text: res.message || "Failed to delete photos",
                //         timer: 3000
                //     });
                //     return;
                // }
                if(res.status == true){
                    swal({
                    type: 'success',
                    title: 'Done',
                    text: `Deleted ${res.deleted_count} photos, kept 1`,
                    timer: 2000,
                    showConfirmButton: false
                    });
                    
                    $('.systemModal').modal('hide');
                    
                    setTimeout(() => {
                        loadDuplicateGroups();
                        buildAlbum();
                    }, 2100);
                } else {
                     swal({
                        type: 'error',
                        title: "Error",
                        text: res.message || "Failed to delete photos",
                        timer: 3000
                    });
                    return;
                }
                
                
            },
            error: function() {
                swal({
                    type: 'error',
                    title: "Error",
                    text: "Failed to process request",
                    timer: 2000
                });
            }
        });
    });
});

// ============================================================
// Action: Mark Group as Not Duplicate
// ============================================================
$(document).on('click', '.mark-group-not-dup', function() {
    let group_id = $(this).data('group-id');
    
    swal({
        title: 'Mark as Not Duplicate',
        text: 'This group will not appear in future scans.',
        type: 'info',
        showCancelButton: true,
        confirmButtonText: 'Confirm',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745',
        closeOnConfirm: false,
        showLoaderOnConfirm: true
    }, function(isConfirm) {
        if (isConfirm) {
            $.ajax({
                url: "/classroom/management/actions/photo.php",
                method: "POST",
                dataType: 'json',
                data: {
                    action: "markGroupNotDuplicate",
                    group_id: group_id
                },
                success: function(res) {

                    if(res.status == true){
                        swal({
                            type: 'success',
                            title: 'Marked',
                            text: 'Group marked as not duplicate',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        
                        setTimeout(() => {
                            loadDuplicateGroups();
                            buildAlbum();
                        }, 1600);
                    } else {
                        swal({
                            type: 'error',
                            title: "Error",
                            text: res.message || "Failed to mark group",
                            timer: 2000
                        });
                        return;
                    }
                }
            });
        }
    });
});

// ============================================================
// Action: Preview Photo (Full Size)
// ============================================================
$(document).on('click', '.photo-preview-thumb', function(e) {
    e.stopPropagation();
    
    let fullImg = $(this).data('full');
    
    $(".modal-preview").modal();
    $(".modal-preview .modal-header").html(`
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h5 class="modal-title"><i class="fa fa-image"></i> Image Preview</h5>
    `);
    $(".modal-preview .modal-body").html(`
        <div style="text-align:center;">
            <img src="${fullImg}" style="max-width:100%; max-height:80vh; border-radius:8px;">
        </div>
    `);
    $(".modal-preview .modal-footer").html(`
        <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
    `);
});

// ============================================================
// Rescan from inside modal
// ============================================================
$(document).on("click", ".scan-duplicates-inline", function() {
    $('.systemModal').modal('hide');
    $('.scan-duplicates').trigger('click');
});


// ===== Continue with rest of the original code (Move Images, Image Table, etc.) =====
// (คัดลอกโค้ดส่วนที่เหลือจาก gallery.js เดิมมาต่อที่นี่)

// Move Selected Images
$(document).on("click", "#moveSelected", function() {
    if (selectedImageIds.length === 0) return;
    showMoveAlbumDialog(selectedImageIds);
});

// Move Single Image
$(document).on("click", ".move-single-img", function() {
    let photo_id = $(this).attr("photo_id");
    showMoveAlbumDialog([photo_id]);
});

function showMoveAlbumDialog(photo_ids) {
    $.ajax({
        url: "/classroom/management/actions/photo.php",
        method: "POST",
        dataType: "json",
        data: {
            action: "buildAlbum",
            classroom_id: classroom_id
        },
        success: function(res) {
            let allAlbums = res.album_data;
            
            let targetAlbums = allAlbums.filter(a => 
                a.album_id != currentAlbumId && !a.is_default
            );
            
            if (targetAlbums.length === 0) {
                swal({
                    type: 'info',
                    title: "No Target Albums",
                    text: "No other user albums available. Please create a new album first.",
                    timer: 3000
                });
                return;
            }
            
            let optionsHtml = '<optgroup label="📁 User Albums">';
            targetAlbums.forEach(a => {
                optionsHtml += `<option value="${a.album_id}">📂 ${a.album_name} (${a.image_count} images)</option>`;
            });
            optionsHtml += '</optgroup>';
            
            swal({
                title: "Move Images",
                html: true,
                text: `
                    <div style="margin:20px 0;">
                        <p style="margin-bottom:15px;">Moving <strong>${photo_ids.length}</strong> image(s) to:</p>
                        <select id="targetAlbum" class="form-control" style="margin:10px auto; max-width:450px; font-size:14px;">
                            ${optionsHtml}
                        </select>
                        <div style="margin-top:15px; padding:12px; background:#fff3cd; border-radius:6px; font-size:12px; text-align:left; border-left:4px solid #ffc107;">
                            <strong><i class="fa fa-exclamation-triangle text-warning"></i> Note:</strong>
                            <ul style="margin:8px 0 0 20px; padding:0; line-height:1.8;">
                                <li>You can only move images to <strong>User Albums</strong></li>
                                <li><strong>Virtual Albums</strong> (Public, Report, Delete, Restrict) automatically show images based on their status</li>
                                <li>This will change the image's <code>album_id</code></li>
                            </ul>
                        </div>
                    </div>
                `,
                type: "info",
                showCancelButton: true,
                confirmButtonText: 'Move',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#28a745',
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            }, function(isConfirm) {
                if (isConfirm) {
                    let targetAlbumId = $('#targetAlbum').val();
                    
                    $.ajax({
                        url: "/classroom/management/actions/photo.php",
                        method: "POST",
                        dataType: "json",
                        data: {
                            action: "moveImages",
                            photo_ids: photo_ids,
                            target_album_id: targetAlbumId
                        },
                        success: function(res) {
                            if (!res.status) {
                                swal({
                                    type: 'error',
                                    title: "Error",
                                    text: res.message || "Cannot move images",
                                    timer: 3000
                                });
                                return;
                            }
                            
                            swal({
                                type: 'success',
                                title: 'Move Complete',
                                text: `${res.moved_count} image(s) moved successfully`,
                                timer: 2000,
                                showConfirmButton: true
                            });
                            
                            selectedImageIds = [];
                            loadImageList(currentAlbumId);
                            buildAlbum();
                        },
                        error: function() {
                            swal({
                                type: 'error',
                                title: "Error",
                                text: "Failed to move images",
                                timer: 2000
                            });
                        }
                    });
                }
            });
        }
    });
}

function getQueueStatusBadge(queue_status, error_msg, photo_id) {
    let badge = '';
    switch(queue_status) {
        case 'pending':
            badge = '<span style="background:#ffc107; color:#000; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:600;"><i class="fa fa-clock"></i> Pending</span>';
            break;
        case 'processing':
            badge = '<span style="background:#17a2b8; color:#fff; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:600;"><i class="fa fa-spinner fa-spin"></i> Processing</span>';
            break;
        case 'completed':
            badge = '<span style="background:#28a745; color:#fff; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:600;"><i class="fa fa-check"></i> Completed</span>';
            break;
        case 'error':
            badge = `<span class="error-status-badge" data-error-msg="${encodeURIComponent(error_msg || 'Unknown error')}" style="background:#dc3545; color:#fff; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:600; cursor:pointer;"><i class="fa fa-exclamation-triangle"></i> Error <i class="fa fa-info-circle"></i></span>`;
            break;
        default:
            badge = '<span style="background:#6c757d; color:#fff; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:600;">Unknown</span>';
    }
    return badge;
}

$(document).on("click", ".error-status-badge", function(e){
    e.stopPropagation();
    
    let error_msg = decodeURIComponent($(this).attr("data-error-msg"));
    
    swal({
        title: "Processing Error",
        html: true,
        text: `<div style="text-align:left;">
                <p><strong>Error Message:</strong></p>
                <div style="background:#f8f9fa; padding:10px; border-radius:4px; border-left:3px solid #dc3545; max-height:300px; overflow-y:auto;">
                    <code style="color:#dc3545; white-space:pre-wrap; word-break:break-word;">${error_msg}</code>
                </div>
               </div>`,
        type: "error",
        confirmButtonText: "Close",
        confirmButtonColor: "#dc3545"
    });
});

function loadImageList(album_id) {
    $.ajax({
        url: "/classroom/management/actions/photo.php",
        method: "POST",
        dataType: "json",
        data: {
            action: "buildImage",
            album_id: album_id
        },
        success: function(res) {
            $(".loader").removeClass("active");
            
            if (res.is_duplicate_album) {
                return;
            }
            
            currentAlbumImages = res.image_data || [];
            selectedImageIds = [];
            currentStatusFilter = 'all';
            currentVisibilityFilter = 'all';
            updateImageTable();
            updateStatusCounts();
            updateVisibilityCounts();
        }
    });
}

function updateStatusCounts() {
    let counts = {
        all: currentAlbumImages.length,
        completed: 0,
        processing: 0,
        pending: 0,
        error: 0
    };
    
    currentAlbumImages.forEach(img => {
        if (counts.hasOwnProperty(img.queue_status)) {
            counts[img.queue_status]++;
        }
    });
    
    $('.count-all').text(counts.all);
    $('.count-completed').text(counts.completed);
    $('.count-processing').text(counts.processing);
    $('.count-pending').text(counts.pending);
    $('.count-error').text(counts.error);
}

function updateVisibilityCounts() {
    let counts = {
        all: currentAlbumImages.length,
        public: 0,
        private: 0
    };
    
    currentAlbumImages.forEach(img => {
        if (img.public == 0) {
            counts.public++;
        } else {
            counts.private++;
        }
    });
    
    $('.count-vis-all').text(counts.all);
    $('.count-vis-public').text(counts.public);
    $('.count-vis-private').text(counts.private);
}

function updateImageTable() {
    let filteredImages = currentAlbumImages;
    
    if (currentStatusFilter !== 'all') {
        filteredImages = filteredImages.filter(img => img.queue_status === currentStatusFilter);
    }
    
    if (currentVisibilityFilter !== 'all') {
        if (currentVisibilityFilter === 'public') {
            filteredImages = filteredImages.filter(img => img.public == 0);
        } else if (currentVisibilityFilter === 'private') {
            filteredImages = filteredImages.filter(img => img.public == 1);
        }
    }
    
    if (filteredImages.length === 0) {
        $('.image-table-container').html(`
            <div class="text-center" style="padding:40px;">
                <i class="fas fa-images fa-3x text-muted"></i>
                <p class="text-muted" style="margin-top:15px;">No images found</p>
            </div>
        `);
        return;
    }
    
    if ($.fn.DataTable.isDataTable('#imageTable')) {
        $('#imageTable').DataTable().destroy();
    }
    
    let html = `
        <div class="table-responsive">
            <table id="imageTable" class="table table-hover table-bordered" style="background:white; width:100%;">
                <thead style="background:#f8f9fa;">
                    <tr>
                        <th width="40" class="text-center">
                            <input type="checkbox" id="selectAllCheckbox">
                        </th>
                        <th width="80" class="text-center">Preview</th>
                        ${currentAlbumInfo && currentAlbumInfo.album_name === 'Duplicates' ? '<th width="120">Duplicate Info</th>' : ''}
                        ${currentAlbumInfo && currentAlbumInfo.is_default && currentAlbumInfo.album_name !== 'Duplicates' ? '<th width="120">Real Album</th>' : ''}
                        <th width="100">Status</th>
                        <th width="100">Visibility</th>
                        <th width="80" class="text-center">Reports</th>
                        <th width="80" class="text-center">Downloads</th>
                        <th>Created Date</th>
                        <th width="200" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    filteredImages.forEach(img => {
        let isChecked = selectedImageIds.includes(img.photo_id) ? 'checked' : '';
        let isDeleted = img.status == 1;
        
        let publicBadge = '';
        if (isDeleted) {
            publicBadge = '<span class="label label-danger"><i class="fa fa-trash"></i> Deleted</span>';
        } else {
            publicBadge = img.public == 0 
                ? '<span class="label label-success"><i class="fa fa-globe"></i> Public</span>' 
                : '<span class="label label-warning"><i class="fa fa-lock"></i> Private</span>';
        }
        
        let reportBadge = img.report_count > 0 
            ? `<span class="badge badge-danger view-reports" photo_id="${img.photo_id}" style="cursor:pointer;background-color: #de0000ba; font-size:12px;">
                   <i class="fa fa-flag"></i> ${img.report_count}
               </span>`
            : '<span class="text-muted">-</span>';
        
        let downloadBadge = img.download_count > 0 
            ? `<span class="badge badge-info view-downloads" photo_id="${img.photo_id}" style="cursor:pointer;background-color: #07c04bba; font-size:12px;">
                   <i class="fa fa-download"></i> ${img.download_count}
               </span>`
            : '<span class="text-muted">-</span>';
        
        let thumbnailHTML = '';
        if (img.thumbnail_300_path && img.thumbnail_300_path !== '') {
            thumbnailHTML = `
                <img src="${img.thumbnail_300_path}" class="img-thumbnail view-img-trigger" 
                     style="max-height:60px; max-width:60px; cursor:pointer;" 
                     data-full-img="${img.photo_path.replace(/_thumbnail(?=\.[^.]+$)/, '')}">
            `;
        } else {
            thumbnailHTML = `
                <div style="width:60px; height:60px; display:flex; align-items:center; justify-content:center; background:#f8f9fa; border:1px solid #dee2e6; border-radius:4px;">
                    <i class="fas fa-spinner fa-spin text-primary" style="font-size:24px;"></i>
                </div>
            `;
        }
        
        // ===== เพิ่ม column สำหรับ Duplicate Info =====
        let duplicateInfoCell = '';
        if (currentAlbumInfo && currentAlbumInfo.album_name === 'Duplicates') {
            duplicateInfoCell = `
                <td>
                    <div style="font-size:11px;">
                        <div style="color:#6f42c1; font-weight:600;">
                            <i class="fa fa-percentage"></i> ${img.similarity || 0}%
                        </div>
                        <div style="color:#28a745;">
                            <i class="fa fa-user"></i> ${img.face_matches || 0} faces
                        </div>
                        ${img.pair_photo_id ? `<div style="color:#999;">Pair: #${img.pair_photo_id}</div>` : ''}
                    </div>
                </td>
            `;
        }
        
        let realAlbumCell = '';
        if (currentAlbumInfo && currentAlbumInfo.is_default && currentAlbumInfo.album_name !== 'Duplicates' && img.real_album_name) {
            realAlbumCell = `<td><span class="label label-default">${img.real_album_name}</span></td>`;
        } else if (currentAlbumInfo && currentAlbumInfo.is_default && currentAlbumInfo.album_name !== 'Duplicates') {
            realAlbumCell = '<td><span class="text-muted">-</span></td>';
        }
        
        let actionButtons = '';
        
        if (isDeleted) {
            actionButtons = `
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-info view-img-btn" data-img="${img.photo_path}" title="View" ${img.thumbnail_300_path ? '' : 'disabled'}>
                        <i class="fa fa-eye"></i>
                    </button>
                    <button class="btn btn-success restore-img" photo_id="${img.photo_id}" title="Restore">
                        <i class="fa fa-undo"></i>
                    </button>
                    <button class="btn btn-default download-img" data-img="${img.photo_path}" title="Download">
                        <i class="fa fa-download"></i>
                    </button>
                </div>
            `;
        } else {
            actionButtons = `
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-info view-img-btn" data-img="${img.photo_path}" title="View" ${img.thumbnail_300_path ? '' : 'disabled'}>
                        <i class="fa fa-eye"></i>
                    </button>
                    <button class="btn btn-purple move-single-img" photo_id="${img.photo_id}" title="Move to Album">
                        <i class="fa fa-folder"></i>
                    </button>
                    <button class="btn ${img.public == 0 ? 'btn-warning' : 'btn-success'} toggle-public" 
                            photo_id="${img.photo_id}" 
                            current_status="${img.public}"
                            title="${img.public == 0 ? 'Set Private' : 'Set Public'}">
                        <i class="fa ${img.public == 0 ? 'fa-lock' : 'fa-globe'}"></i>
                    </button>
                    <button class="btn btn-danger delete-img" photo_id="${img.photo_id}" title="Delete">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            `;
        }
        
        html += `
            <tr class="image-row" data-photo-id="${img.photo_id}">
                <td class="text-center">
                    <input type="checkbox" class="image-checkbox" value="${img.photo_id}" ${isChecked}>
                </td>
                <td class="text-center">
                    ${thumbnailHTML}
                </td>
                ${duplicateInfoCell}
                ${realAlbumCell}
                <td>${getQueueStatusBadge(img.queue_status, img.error_msg)}</td>
                <td>${publicBadge}</td>
                <td class="text-center">${reportBadge}</td>
                <td class="text-center">${downloadBadge}</td>
                <td><small>${img.date_create}</small></td>
                <td class="text-center">
                    ${actionButtons}
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    $('.image-table-container').html(html);
    
    $('#imageTable').DataTable({
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "order": [[currentAlbumInfo && currentAlbumInfo.is_default ? 7 : 6, "desc"]],
        "columnDefs": [
            { "orderable": false, "targets": [0, 1, currentAlbumInfo && currentAlbumInfo.is_default ? 8 : 7] },
            { "searchable": false, "targets": [0, 1, currentAlbumInfo && currentAlbumInfo.is_default ? 8 : 7] }
        ],
        "language": {
            "search": "Search:",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            },
            "emptyTable": "No images available"
        },
        "drawCallback": function() {
            updateSelectedCount();
        }
    });
    
    updateSelectedCount();
}

$(document).on("click", ".restore-img", function() {
    let photo_id = $(this).attr("photo_id");
    let album_id = currentAlbumId;
    
    swal({
        html: true,
        title: "Restore Image",
        text: 'Do you want to restore this image?',
        type: "info",
        showCancelButton: true,
        closeOnConfirm: false,
        confirmButtonText: 'Yes, Restore',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#CCCCCC',
        showLoaderOnConfirm: true,
    }, function(isConfirm) {
        if (isConfirm) {
            $.ajax({
                url: "/classroom/management/actions/photo.php",
                method: "POST",
                data: {
                    action: "restoreImage",
                    photo_id: photo_id
                },
                dataType: "json",
                success: function(res) {
                    if (res.status) {
                        swal({
                            type: 'success',
                            title: "Restored",
                            text: "Image has been restored successfully",
                            showConfirmButton: false,
                            timer: 1500
                        });
                        
                        // รีโหลดรายการรูป
                        loadImageList(album_id);
                        buildAlbum();
                    } else {
                        swal({
                            type: 'error',
                            title: "Error",
                            text: res.message || "Cannot restore image",
                            timer: 2000
                        });
                    }
                },
                error: function() {
                    swal({
                        type: 'error',
                        title: "Error",
                        text: "Failed to restore image",
                        timer: 2000
                    });
                }
            });
        } else {
            swal.close();
        }
    });
});

// Action: Download Single Image from Table
$(document).on("click", ".download-img", function() {
    let img = $(this).data('img');
    let fullImg = img.replace(/_thumbnail(?=\.[^.]+$)/, '');
    
    let link = document.createElement('a');
    link.href = fullImg;
    link.download = fullImg.split('/').pop();
    link.click();
    
    swal({
        type: 'success',
        title: 'Downloading...',
        text: '',
        showConfirmButton: false,
        timer: 1000
    });
});

function updateSelectedCount() {
    $('.selected-count').text(selectedImageIds.length);
    $('#downloadSelected, #deleteSelected, #moveSelected').prop('disabled', selectedImageIds.length === 0);
    
    let allVisible = $('.image-checkbox').length;
    let allChecked = $('.image-checkbox:checked').length;
    $('#selectAllCheckbox').prop('checked', allVisible > 0 && allVisible === allChecked);
}

$(document).on('click', '.filter-status', function() {
    $('.filter-status').removeClass('active');
    $(this).addClass('active');
    currentStatusFilter = $(this).data('status');
    updateImageTable();
});

$(document).on('click', '.filter-visibility', function() {
    $('.filter-visibility').removeClass('active');
    $(this).addClass('active');
    currentVisibilityFilter = $(this).data('visibility');
    updateImageTable();
});

$(document).on('change', '#selectAllCheckbox', function() {
    let isChecked = $(this).is(':checked');
    $('.image-checkbox').prop('checked', isChecked).trigger('change');
});

$(document).on('change', '.image-checkbox', function() {
    let photoId = $(this).val();
    if ($(this).is(':checked')) {
        if (!selectedImageIds.includes(photoId)) {
            selectedImageIds.push(photoId);
        }
    } else {
        selectedImageIds = selectedImageIds.filter(id => id !== photoId);
    }
    updateSelectedCount();
});

$(document).on('click', '#selectAllImages', function() {
    let allChecked = $('.image-checkbox:checked').length === $('.image-checkbox').length;
    $('.image-checkbox').prop('checked', !allChecked).trigger('change');
});

$(document).on('click', '#downloadSelected', function() {
    if (selectedImageIds.length === 0) return;
    
    swal({
        title: "Download Images",
        text: `Download ${selectedImageIds.length} selected images?`,
        type: "info",
        showCancelButton: true,
        confirmButtonText: 'Download',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745'
    }, function(isConfirm) {
        if (isConfirm) {
            selectedImageIds.forEach((photoId, index) => {
                let img = currentAlbumImages.find(i => i.photo_id == photoId);
                if (img) {
                    setTimeout(() => {
                        let fullImg = img.photo_path.replace(/_thumbnail(?=\.[^.]+$)/, '');
                        let link = document.createElement('a');
                        link.href = fullImg;
                        link.download = `image_${photoId}.jpg`;
                        link.click();
                    }, index * 200);
                }
            });
            
            swal({
                type: 'success',
                title: 'Download Started',
                text: `Downloading ${selectedImageIds.length} images...`,
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
});

$(document).on('click', '#deleteSelected', function() {
    if (selectedImageIds.length === 0) return;
    
    let album_id = currentAlbumId;
    
    swal({
        title: "Delete Images",
        text: `Are you sure you want to delete ${selectedImageIds.length} selected images?`,
        type: "warning",
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        closeOnConfirm: false,
        showLoaderOnConfirm: true
    }, function(isConfirm) {
        if (isConfirm) {
            let deletePromises = selectedImageIds.map(photoId => {
                return $.ajax({
                    url: "/classroom/management/actions/photo.php",
                    method: "POST",
                    data: {
                        action: "deleteImage",
                        photo_id: photoId
                    }
                });
            });
            
            Promise.all(deletePromises).then(() => {
                swal({
                    type: 'success',
                    title: 'Deleted Successfully',
                    text: `${selectedImageIds.length} images deleted`,
                    timer: 1500,
                    showConfirmButton: false
                });
                selectedImageIds = [];
                loadImageList(album_id);
                buildAlbum();
            });
        }
    });
});

$(document).on('click', '.view-img-trigger', function() {
    let fullImg = $(this).data('full-img');
    let photoId = $(this).closest('.image-row').data('photo-id');
    showImageModal(fullImg, photoId);
});

$(document).on('click', '.view-img-btn', function() {
    let img = $(this).data('img');
    let fullImg = img.replace(/_thumbnail(?=\.[^.]+$)/, '');
    let photoId = $(this).closest('.image-row').data('photo-id');
    showImageModal(fullImg, photoId);
});

// Global variables for image navigation
let currentImageIndex = 0;
let currentImageList = [];

function showImageModal(imgUrl, photoId = null) {
    // เตรียมข้อมูลรูปทั้งหมด (ตาม filter ปัจจุบัน)
    let filteredImages = currentAlbumImages;
    
    if (currentStatusFilter !== 'all') {
        filteredImages = filteredImages.filter(img => img.queue_status === currentStatusFilter);
    }
    
    if (currentVisibilityFilter !== 'all') {
        if (currentVisibilityFilter === 'public') {
            filteredImages = filteredImages.filter(img => img.public == 0);
        } else if (currentVisibilityFilter === 'private') {
            filteredImages = filteredImages.filter(img => img.public == 1);
        }
    }
    
    currentImageList = filteredImages;
    
    // หา index ของรูปที่เปิด
    if (photoId) {
        currentImageIndex = currentImageList.findIndex(img => img.photo_id == photoId);
        if (currentImageIndex === -1) currentImageIndex = 0;
    } else {
        // ถ้าไม่มี photoId ให้หาจาก imgUrl
        currentImageIndex = currentImageList.findIndex(img => {
            let fullImg = img.photo_path.replace(/_thumbnail(?=\.[^.]+$)/, '');
            return fullImg === imgUrl;
        });
        if (currentImageIndex === -1) currentImageIndex = 0;
    }
    
    renderImageModal();
}

function renderImageModal() {
    if (currentImageList.length === 0) {
        swal({
            type: 'warning',
            title: 'No Images',
            text: 'No images available to display',
            timer: 2000
        });
        return;
    }
    
    let currentImage = currentImageList[currentImageIndex];
    let fullImg = currentImage.photo_path.replace(/_thumbnail(?=\.[^.]+$)/, '');
    
    // เช็คว่ารูปถูกลบหรือไม่ (อยู่ใน Delete album)
    let isDeleted = currentImage.status == 1;
    
    // สร้าง Action Buttons
    let actionButtons = '';
    
    // ปุ่ม Previous
    if (currentImageList.length > 1) {
        actionButtons += `
            <button type="button" class="btn btn-default btn-nav-prev" ${currentImageIndex === 0 ? 'disabled' : ''}>
                <i class="fa fa-chevron-left"></i> Previous
            </button>
        `;
    }
    
    // ปุ่ม Restore (แสดงเฉพาะรูปที่ถูกลบ)
    if (isDeleted) {
        actionButtons += `
            <button type="button" class="btn btn-success btn-restore-img" photo_id="${currentImage.photo_id}">
                <i class="fa fa-undo"></i> Restore
            </button>
        `;
    } else {
        // ปุ่ม Toggle Public/Private (แสดงเฉพาะรูปที่ไม่ถูกลบ)
        if (currentImage.public == 0) {
            actionButtons += `
                <button type="button" class="btn btn-warning btn-toggle-public-modal" photo_id="${currentImage.photo_id}" current_status="0">
                    <i class="fa fa-lock"></i> Set Private
                </button>
            `;
        } else {
            actionButtons += `
                <button type="button" class="btn btn-success btn-toggle-public-modal" photo_id="${currentImage.photo_id}" current_status="1">
                    <i class="fa fa-globe"></i> Set Public
                </button>
            `;
        }
        
        // ปุ่ม Delete (แสดงเฉพาะรูปที่ไม่ถูกลบ)
        actionButtons += `
            <button type="button" class="btn btn-danger btn-delete-img-modal" photo_id="${currentImage.photo_id}">
                <i class="fa fa-trash"></i> Delete
            </button>
        `;
    }
    
    // ปุ่ม Download
    actionButtons += `
        <button type="button" class="btn btn-info btn-download-img-modal" data-img="${fullImg}">
            <i class="fa fa-download"></i> Download
        </button>
    `;
    
    // ปุ่ม Next
    if (currentImageList.length > 1) {
        actionButtons += `
            <button type="button" class="btn btn-default btn-nav-next" ${currentImageIndex === currentImageList.length - 1 ? 'disabled' : ''}>
                Next <i class="fa fa-chevron-right"></i>
            </button>
        `;
    }
    
    // ปุ่ม Close
    actionButtons += `
        <button type="button" class="btn btn-white" data-dismiss="modal">
            <i class="fa fa-times"></i> Close
        </button>
    `;
    
    // สร้าง Status Badges
    let statusBadges = '';
    if (isDeleted) {
        statusBadges += '<span class="label label-danger" style="margin-right:5px;"><i class="fa fa-trash"></i> Deleted</span>';
    } else {
        statusBadges += currentImage.public == 0 
            ? '<span class="label label-success" style="margin-right:5px;"><i class="fa fa-globe"></i> Public</span>' 
            : '<span class="label label-warning" style="margin-right:5px;"><i class="fa fa-lock"></i> Private</span>';
    }
    
    if (currentImage.report_count > 0) {
        statusBadges += `<span class="label label-danger" style="margin-right:5px;"><i class="fa fa-flag"></i> ${currentImage.report_count} Reports</span>`;
    }
    
    statusBadges += getQueueStatusBadge(currentImage.queue_status, currentImage.error_msg);
    
    $(".modal-preview").modal();
    
    $(".modal-preview .modal-header").html(`
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h5 class="modal-title">
            <i class="fa fa-image"></i> Image Viewer 
            ${currentImageList.length > 1 ? `<span style="color:#999; font-size:14px;">(${currentImageIndex + 1} / ${currentImageList.length})</span>` : ''}
        </h5>
        <div style="margin-top:8px;">
            ${statusBadges}
        </div>
    `);
    
    $(".modal-preview .modal-body").html(`
        <div class="image-viewer-container" style="position:relative; min-height:400px; background:#000; border-radius:8px; overflow:hidden;">
            <!-- Navigation Arrows -->
            ${currentImageList.length > 1 && currentImageIndex > 0 ? `
                <div class="nav-arrow nav-arrow-left" style="position:absolute; left:20px; top:50%; transform:translateY(-50%); z-index:10; cursor:pointer; background:rgba(255,255,255,0.9); width:50px; height:50px; border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 12px rgba(0,0,0,0.3); transition:all 0.3s;">
                    <i class="fa fa-chevron-left" style="font-size:24px; color:#333;"></i>
                </div>
            ` : ''}
            
            ${currentImageList.length > 1 && currentImageIndex < currentImageList.length - 1 ? `
                <div class="nav-arrow nav-arrow-right" style="position:absolute; right:20px; top:50%; transform:translateY(-50%); z-index:10; cursor:pointer; background:rgba(255,255,255,0.9); width:50px; height:50px; border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 12px rgba(0,0,0,0.3); transition:all 0.3s;">
                    <i class="fa fa-chevron-right" style="font-size:24px; color:#333;"></i>
                </div>
            ` : ''}
            
            <!-- Main Image -->
            <img src="${fullImg}" class="img-responsive center-block" style="max-width:100%; max-height:80vh; height:auto; display:block; margin:0 auto;">
            
            <!-- Image Info Overlay -->
            <div style="position:absolute; bottom:0; left:0; right:0; background:linear-gradient(to top, rgba(0,0,0,0.8), transparent); padding:20px 15px 15px; color:white;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <small><i class="fa fa-calendar"></i> ${currentImage.date_create}</small>
                    </div>
                    <div>
                        ${currentImage.report_count > 0 ? `
                            <span class="view-reports" photo_id="${currentImage.photo_id}" style="cursor:pointer; background:rgba(220,53,69,0.9); padding:5px 10px; border-radius:15px; margin-right:5px;">
                                <i class="fa fa-flag"></i> ${currentImage.report_count} Reports
                            </span>
                        ` : ''}
                        ${currentImage.download_count > 0 ? `
                            <span class="view-downloads" photo_id="${currentImage.photo_id}" style="cursor:pointer; background:rgba(23,162,184,0.9); padding:5px 10px; border-radius:15px;">
                                <i class="fa fa-download"></i> ${currentImage.download_count} Downloads
                            </span>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `);
    
    $(".modal-preview .modal-footer").html(actionButtons);
    
    // Add hover effects for navigation arrows
    if (!$('#nav-arrow-style').length) {
        $('<style id="nav-arrow-style">')
            .text(`
                .nav-arrow:hover {
                    background:rgba(255,255,255,1) !important;
                    transform:translateY(-50%) scale(1.1);
                    box-shadow:0 6px 16px rgba(0,0,0,0.4);
                }
                .nav-arrow:active {
                    transform:translateY(-50%) scale(0.95);
                }
            `)
            .appendTo('head');
    }
}

// Navigation: Previous Image
$(document).on("click", ".btn-nav-prev, .nav-arrow-left", function() {
    if (currentImageIndex > 0) {
        currentImageIndex--;
        renderImageModal();
    }
});

// Navigation: Next Image
$(document).on("click", ".btn-nav-next, .nav-arrow-right", function() {
    if (currentImageIndex < currentImageList.length - 1) {
        currentImageIndex++;
        renderImageModal();
    }
});

// Keyboard Navigation
$(document).on('keydown', function(e) {
    if ($('.modal-preview').hasClass('in')) {
        if (e.keyCode === 37) { // Left Arrow
            $('.btn-nav-prev').click();
        } else if (e.keyCode === 39) { // Right Arrow
            $('.btn-nav-next').click();
        } else if (e.keyCode === 27) { // Escape
            $('.modal-preview').modal('hide');
        }
    }
});

// Action: Toggle Public/Private from Modal
$(document).on("click", ".btn-toggle-public-modal", function() {
    let photo_id = $(this).attr("photo_id");
    let current_status = $(this).attr("current_status");
    let new_status = current_status == 1 ? 0 : 1;
    
    $(".loader").addClass("active");
    
    $.ajax({
        url: "/classroom/management/actions/photo.php",
        method: "POST",
        data: {
            action: "togglePublic",
            photo_id: photo_id,
            public_status: new_status
        },
        dataType: "json",
        success: function(res) {
            $(".loader").removeClass("active");
            if (res.status) {
                swal({
                    type: 'success',
                    title: new_status == 0 ? "Set as Public" : "Set as Private",
                    text: "",
                    showConfirmButton: false,
                    timer: 1000
                });
                
                // อัพเดทข้อมูลรูปใน currentImageList
                currentImageList[currentImageIndex].public = new_status;
                
                // รีเฟรช modal
                renderImageModal();
                
                // รีโหลดรายการรูป
                loadImageList(currentAlbumId);
                buildAlbum();
            }
        }
    });
});

// Action: Delete Image from Modal
$(document).on("click", ".btn-delete-img-modal", function() {
    let photo_id = $(this).attr("photo_id");
    
    swal({
        html: true,
        title: window.lang.translate("Are you sure?"),
        text: 'You want to delete this image?',
        type: "warning",
        showCancelButton: true,
        closeOnConfirm: false,
        confirmButtonText: window.lang.translate('Yes'),
        cancelButtonText: window.lang.translate("Cancel"),
        confirmButtonColor: '#FF9900',
        cancelButtonColor: '#CCCCCC',
        showLoaderOnConfirm: true,
    }, function(isConfirm) {
        if (isConfirm) {
            $.ajax({
                url: "/classroom/management/actions/photo.php",
                method: "POST",
                data: {
                    action: "deleteImage",
                    photo_id: photo_id
                },
                success: function() {
                    swal({
                        type: 'success',
                        title: "Deleted",
                        text: "",
                        showConfirmButton: false,
                        timer: 1000
                    });
                    
                    // ลบรูปออกจาก currentImageList
                    currentImageList.splice(currentImageIndex, 1);
                    
                    // ถ้ายังมีรูปเหลือ
                    if (currentImageList.length > 0) {
                        // ปรับ index ถ้าลบรูปสุดท้าย
                        if (currentImageIndex >= currentImageList.length) {
                            currentImageIndex = currentImageList.length - 1;
                        }
                        renderImageModal();
                    } else {
                        // ถ้าไม่มีรูปแล้ว ปิด modal
                        $(".modal-preview").modal('hide');
                    }
                    
                    loadImageList(currentAlbumId);
                    buildAlbum();
                }
            });
        } else {
            swal.close();
        }
    });
});

// Action: Restore Image from Modal
$(document).on("click", ".btn-restore-img", function() {
    let photo_id = $(this).attr("photo_id");
    
    swal({
        html: true,
        title: "Restore Image",
        text: 'Do you want to restore this image?',
        type: "info",
        showCancelButton: true,
        closeOnConfirm: false,
        confirmButtonText: 'Yes, Restore',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#CCCCCC',
        showLoaderOnConfirm: true,
    }, function(isConfirm) {
        if (isConfirm) {
            $.ajax({
                url: "/classroom/management/actions/photo.php",
                method: "POST",
                data: {
                    action: "restoreImage",
                    photo_id: photo_id
                },
                dataType: "json",
                success: function(res) {
                    if (res.status) {
                        swal({
                            type: 'success',
                            title: "Restored",
                            text: "Image has been restored successfully",
                            showConfirmButton: false,
                            timer: 1500
                        });
                        
                        // อัพเดทสถานะรูปใน currentImageList
                        currentImageList[currentImageIndex].status = 0;
                        
                        // รีเฟรช modal
                        renderImageModal();
                        
                        // รีโหลดรายการรูป
                        loadImageList(currentAlbumId);
                        buildAlbum();
                    } else {
                        swal({
                            type: 'error',
                            title: "Error",
                            text: res.message || "Cannot restore image",
                            timer: 2000
                        });
                    }
                }
            });
        } else {
            swal.close();
        }
    });
});

// Action: Download Image from Modal
$(document).on("click", ".btn-download-img-modal", function() {
    let imgUrl = $(this).data('img');
    let link = document.createElement('a');
    link.href = imgUrl;
    link.download = imgUrl.split('/').pop();
    link.click();
    
    swal({
        type: 'success',
        title: 'Downloading...',
        text: '',
        showConfirmButton: false,
        timer: 1000
    });
});



$(document).on("click", ".view-reports", function(){
    let photo_id = $(this).attr("photo_id");
    
    $(".loader").addClass("active");
    
    $.ajax({
        url: "/classroom/management/actions/photo.php",
        method: "POST",
        dataType: "json",
        data: {
            action: "getReportDetails",
            photo_id: photo_id,
            classroom_id: classroom_id
        },
        success: function(res){
            $(".loader").removeClass("active");
            
            $(".modal-preview").modal();
            
            const statusBadge = res.photo_public == 1 
                ? '<span class="badge badge-warning"><i class="fa fa-lock"></i> Private</span>'
                : '<span class="badge badge-success"><i class="fa fa-globe"></i> Public</span>';
            
            $(".modal-preview .modal-header").html(`
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h5 class="modal-title">
                    <i class="fa fa-flag text-danger"></i> Report Details (${res.reports.length} reports)
                    ${statusBadge}
                </h5>
            `);
            
            let html = '<div class="table-responsive"><table class="table table-striped table-bordered">';
            html += '<thead><tr><th>#</th><th style="width: 50px;">Profile</th><th>User</th><th>Reason</th><th>Date</th></tr></thead><tbody>';
            
            const defaultProfile = 'path/to/default/profile/image.png';

            if(res.reports && res.reports.length > 0) {
                $.each(res.reports, function(i, report){
                    const profile_img_src = report.profile_image_url || defaultProfile;
                    html += `
                        <tr>
                            <td>${i+1}</td>
                            <td><img src="${profile_img_src}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;"></td>
                            <td>${report.full_name || '-'}</td>
                            <td>${report.report_reason}</td>
                            <td>${report.created_at}</td>
                        </tr>
                    `;
                });
            } else {
                html += '<tr><td colspan="5" class="text-center text-muted">No reports found</td></tr>';
            }
            
            html += '</tbody></table></div>';
            
            $(".modal-preview .modal-body").html(html);
            
            const toggleBtn = res.photo_public == 1
                ? `<button type="button" class="btn btn-success toggle-public-from-report" photo_id="${photo_id}" current_status="1">
                    <i class="fa fa-globe"></i> Set as Public
                   </button>`
                : `<button type="button" class="btn btn-warning toggle-public-from-report" photo_id="${photo_id}" current_status="0">
                    <i class="fa fa-lock"></i> Set as Private
                   </button>`;
            
            $(".modal-preview .modal-footer").html(`
                ${toggleBtn}
                <button type="button" class="btn btn-danger delete-from-report" photo_id="${photo_id}">
                    <i class="fa fa-trash"></i> Delete Image
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            `);
        }
    });
});

$(document).on("click", ".toggle-public-from-report", function(){
    let photo_id = $(this).attr("photo_id");
    let album_id = currentAlbumId;
    let current_status = $(this).attr("current_status");
    let new_status = current_status == 1 ? 0 : 1;
    
    $(".loader").addClass("active");
    
    $.ajax({
        url: "/classroom/management/actions/photo.php",
        method: "POST",
        data: {
            action: "togglePublic",
            photo_id: photo_id,
            public_status: new_status
        },
        dataType: "json",
        success: function(res){
            $(".loader").removeClass("active");
            if(res.status) {
                swal({
                    type: 'success',
                    title: new_status == 1 ? "Set as Private" : "Set as Public",
                    text: new_status == 1 ? "This image is now private" : "This image is now visible to others",
                    showConfirmButton: false,
                    timer: 1500
                });
                $(".modal-preview").modal('hide');
                loadImageList(album_id);
                buildAlbum();
            }
        },
        error: function(){
            $(".loader").removeClass("active");
            swal({
                type: 'error',
                title: "Error",
                text: "Failed to update status",
                timer: 2000
            });
        }
    });
});

$(document).on("click", ".delete-from-report", function(){
    let photo_id = $(this).attr("photo_id");
    let album_id = currentAlbumId;
    
    swal({
        html: true,
        title: window.lang.translate("Are you sure?"),
        text: 'You want to delete this Image?',
        type: "warning",
        showCancelButton: true,
        closeOnConfirm: false,
        confirmButtonText: window.lang.translate('Yes'),
        cancelButtonText: window.lang.translate("Cancel"),  
        confirmButtonColor: '#FF9900',
        cancelButtonColor: '#CCCCCC',
        showLoaderOnConfirm: true,
    },
    function(isConfirm){
        if (isConfirm) {
            $.ajax({
                url: "/classroom/management/actions/photo.php",
                method: "POST",
                data: {
                    action: "deleteImage",
                    photo_id: photo_id
                },
                success: function(){
                    swal({
                        type: 'success',
                        title: "Delete Complete",
                        text: "",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $(".modal-preview").modal('hide');
                    loadImageList(album_id);
                    buildAlbum();
                }
            });
        } else {
            swal.close();
        }   
    });
});

$(document).on("click", ".view-downloads", function(){
    let photo_id = $(this).attr("photo_id");
    
    $(".loader").addClass("active");
    
    $.ajax({
        url: "/classroom/management/actions/photo.php",
        method: "POST",
        dataType: "json",
        data: {
            action: "getDownloadDetails",
            photo_id: photo_id,
            classroom_id: classroom_id
        },
        success: function(res){
            $(".loader").removeClass("active");
            
            $(".modal-preview").modal();
            $(".modal-preview .modal-header").html(`
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h5 class="modal-title"><i class="fa fa-download text-info"></i> Download Details (${res.downloads.length} users)</h5>
            `);
            
            let html = '<div class="table-responsive"><table class="table table-striped table-bordered">';
            html += '<thead><tr><th>#</th><th style="width: 50px;">Profile</th><th>User</th><th>Times Downloaded</th><th>Last Downloaded</th></tr></thead><tbody>';
            
            const defaultProfile = 'path/to/default/profile/image.png';

            if(res.downloads && res.downloads.length > 0) {
               $.each(res.downloads, function(i, dl){
                    const profile_img_src = dl.profile_image_url || defaultProfile;
                    html += `
                        <tr>
                            <td>${i+1}</td>
                            <td><img src="${profile_img_src}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;"></td>
                            <td>${dl.full_name || '-'}</td>
                            <td><span class="badge badge-primary">${dl.download_count}</span></td>
                            <td>${dl.downloaded_at}</td>
                        </tr>
                    `;
                });
            } else {
                html += '<tr><td colspan="5" class="text-center text-muted">No downloads yet</td></tr>';
            }
            
            html += '</tbody></table></div>';
            
            $(".modal-preview .modal-body").html(html);
            $(".modal-preview .modal-footer").html(`
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            `);
        }
    });
});

$(document).on("click", ".toggle-public", function(){
    let photo_id = $(this).attr("photo_id");
    let album_id = currentAlbumId;
    let current_status = $(this).attr("current_status");
    let new_status = current_status == 1 ? 0 : 1;
    
    $(".loader").addClass("active");
    
    $.ajax({
        url: "/classroom/management/actions/photo.php",
        method: "POST",
        data: {
            action: "togglePublic",
            photo_id: photo_id,
            public_status: new_status
        },
        dataType: "json",
        success: function(res){
            $(".loader").removeClass("active");
            if(res.status) {
                swal({
                    type: 'success',
                    title: new_status == 0 ? "Set as Public" : "Set as Private",
                    text: new_status == 0 ? "This image is now visible to others" : "This image is now private",
                    showConfirmButton: false,
                    timer: 1500
                });
                loadImageList(album_id);
                buildAlbum();
            }
        },
        error: function(){
            $(".loader").removeClass("active");
            swal({
                type: 'error',
                title: "Error",
                text: "Failed to update status",
                timer: 2000
            });
        }
    });
});

$(document).on("click", ".delete-img", function(){
    let photo_id = $(this).attr("photo_id");
    let album_id = currentAlbumId;
    swal({
        html: true,
        title: window.lang.translate("Are you sure?"),
        text: 'You want to delete this Image?',
        type: "warning",
        showCancelButton: true,
        closeOnConfirm: false,
        confirmButtonText: window.lang.translate('Yes'),
        cancelButtonText: window.lang.translate("Cancel"),	
        confirmButtonColor: '#FF9900',
        cancelButtonColor: '#CCCCCC',
        showLoaderOnConfirm: true,
    },
    function(isConfirm){
        if (isConfirm) {
            $.ajax({
                url: "/classroom/management/actions/photo.php",
                method: "POST",
                data: {
                    action: "deleteImage",
                    photo_id: photo_id
                },
                success: function(){
                    swal({
                        type: 'success',
                        title: "Delete Complete",
                        text: "",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    loadImageList(album_id);
                    buildAlbum();
                }
            });
        } else {
            swal.close();
        }   
    });
});

$(document).on("click", ".btn-upload-select", function () {
    $("#uploadInput").click();
});

$(document).on("change", "#uploadInput", function () {
    addFilesToList(this.files);
});

$(document).on("dragover", ".upload-section", function(e){
    e.preventDefault();
    e.stopPropagation();
    $(this).css("background", "#f0f4ff");
});

$(document).on("dragleave", ".upload-section", function(e){
    e.preventDefault();
    e.stopPropagation();
    $(this).css("background", "#fafafa");
});

$(document).on("drop", ".upload-section", function(e){
    e.preventDefault();
    e.stopPropagation();
    $(this).css("background", "#fafafa");
    addFilesToList(e.originalEvent.dataTransfer.files);
});

function addFilesToList(files) {
    let rejected = [];
    
    for (let i = 0; i < files.length; i++) {
        let file = files[i];
        
        if (selectedFiles.length >= MAX_FILES) {
            alert(`Maximum ${MAX_FILES} files allowed.`);
            break;
        }
        
        let fileName = file.name;
        let ext = fileName.split('.').pop().toLowerCase();
        
        if (!ALLOWED_EXTENSIONS.includes(ext)) {
            rejected.push(`${fileName}: Invalid type (allowed: ${ALLOWED_EXTENSIONS.join(', ')})`);
            continue;
        }
        
        if (file.size > MAX_FILE_SIZE) {
            let sizeMB = (file.size / (1024 * 1024)).toFixed(2);
            let maxMB = (MAX_FILE_SIZE / (1024 * 1024)).toFixed(2);
            rejected.push(`${fileName}: Too large (${sizeMB}MB, max: ${maxMB}MB)`);
            continue;
        }
        
        if (!file.type.startsWith('image/')) {
            rejected.push(`${fileName}: Not an image file`);
            continue;
        }
        
        selectedFiles.push(file);
    }
    
    if (rejected.length > 0) {
        swal({
            type: 'warning',
            title: "Some files rejected",
            html: true,
            text: rejected.join('<br>'),
            confirmButtonText: 'OK'
        });
    }
    
    renderFileList();
}

function renderFileList() {
    if (selectedFiles.length === 0) {
        $(".pending-files").hide();
        return;
    }
    let html = "";
    selectedFiles.forEach((f, idx) => {
        html += `<li style="padding:2px 0;"><i class="fa fa-image text-primary"></i> ${idx + 1}. ${f.name}</li>`;
    });
    $(".file-list").html(html);
    $(".file-count").text(selectedFiles.length);
    $(".pending-files").show();
}

$(document).on("click", ".btn-upload-start", async function () {
    let album_id = $(this).attr("album_id");
    
    if (selectedFiles.length === 0) {
        swal({
            type: 'warning',
            title: "No Files",
            text: "Please select files to upload.",
            timer: 2000
        });
        return;
    }
    
    $(".loader").addClass("active");
    $(".upload-status").show().html('<i class="fas fa-spinner fa-spin"></i> Preparing upload...');
    
    const batchSize = 10;
    let totalFiles = selectedFiles.length;
    let uploadedFiles = 0;
    let allErrors = [];
    let allDetailedErrors = {};
    let totalSuccess = 0;
    
    for (let b = 0; b < totalFiles; b += batchSize) {
        const batchFiles = selectedFiles.slice(b, b + batchSize);
        const formData = new FormData();
        
        for (let i = 0; i < batchFiles.length; i++) {
            formData.append("files[]", batchFiles[i]);
        }
        
        formData.append("action", "uploadImages");
        formData.append("album_id", album_id);
        formData.append("classroom_id", classroom_id);
        
        const batchNum = Math.floor(b / batchSize) + 1;
        const totalBatches = Math.ceil(totalFiles / batchSize);
        
        $(".upload-status").html(`
            <i class="fas fa-spinner fa-spin"></i> 
            Uploading batch ${batchNum}/${totalBatches} 
            (${uploadedFiles}/${totalFiles} files)
        `);
        
        try {
            const result = await $.ajax({
                url: "/classroom/management/actions/photo.php",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                timeout: 120000
            });
            
            uploadedFiles += batchFiles.length;
            totalSuccess += result.success_count || 0;
            
            if (result.errors && result.errors.length > 0) {
                allErrors = allErrors.concat(result.errors);
            }
            
            if (result.detailed_errors) {
                for (let errorType in result.detailed_errors) {
                    if (!allDetailedErrors[errorType]) {
                        allDetailedErrors[errorType] = [];
                    }
                    allDetailedErrors[errorType] = allDetailedErrors[errorType].concat(
                        result.detailed_errors[errorType]
                    );
                }
            }
            
        } catch (error) {
            console.error('Upload error:', error);
            
            if (error.statusText === 'timeout') {
                allErrors.push(`Batch ${batchNum} timeout (${batchFiles.length} files)`);
            } else {
                allErrors.push(`Batch ${batchNum} failed: ${error.statusText || error}`);
            }
            
            uploadedFiles += batchFiles.length;
        }
        
        const percent = Math.round((uploadedFiles / totalFiles) * 100);
        $(".upload-status").html(`
            <i class="fas fa-spinner fa-spin"></i> 
            Uploading ${percent}% 
            (${totalSuccess} succeeded, ${allErrors.length} errors)
        `);
    }
    
    $(".loader").removeClass("active");
    $(".upload-status").hide();
    
    let errorSummaryHtml = '';
    if (Object.keys(allDetailedErrors).length > 0) {
        errorSummaryHtml = '<div style="text-align:left; margin-top:15px;">';
        errorSummaryHtml += '<strong>Error Breakdown:</strong><br>';
        
        const errorLabels = {
            'upload_error': '❌ Upload Failed',
            'invalid_extension': '📄 Invalid File Type',
            'too_large': '📦 File Too Large',
            'invalid_mime': '🔍 Invalid Format',
            'invalid_image': '🖼️ Corrupted Image',
            'temp_copy_failed': '💾 Temp Copy Failed',
            'logo_failed': '🏷️ Logo Not Added',
            'storage_save_failed': '☁️ Storage Save Failed',
            'storage_exception': '⚠️ Storage Error',
            'temp_dir_failed': '📁 Temp Dir Failed',
            'temp_queue_failed': '⏳ Queue Failed',
            'db_insert_failed': '🗄️ Database Insert Failed',
            'db_exception': '💥 Database Error',
            'queue_insert_failed': '📋 Queue Insert Failed'
        };
        
        for (let errorType in allDetailedErrors) {
            const count = allDetailedErrors[errorType].length;
            const label = errorLabels[errorType] || errorType;
            errorSummaryHtml += `<div style="margin:5px 0; padding:5px; background:#f8f9fa; border-left:3px solid #dc3545; font-size:13px;">`;
            errorSummaryHtml += `${label}: <strong>${count} files</strong>`;
            
            if (count <= 3) {
                errorSummaryHtml += `<br><small style="color:#666;">${allDetailedErrors[errorType].join(', ')}</small>`;
            } else {
                errorSummaryHtml += `<br><small style="color:#666;">${allDetailedErrors[errorType].slice(0, 3).join(', ')}... and ${count - 3} more</small>`;
            }
            
            errorSummaryHtml += `</div>`;
        }
        errorSummaryHtml += '</div>';
    }
    
    if (totalSuccess === totalFiles) {
        swal({
            type: 'success',
            title: "Upload Complete",
            html: true,
            text: `<div style="font-size:16px;">
                        Successfully uploaded <strong>${totalSuccess} / ${totalFiles}</strong> files!<br>
                       <small style="color:#28a745;">Images will be resized in background queue</small>
                   </div>`,
            confirmButtonText: 'OK'
        });
    } else if (totalSuccess > 0) {
        swal({
            type: 'warning',
            title: "Partial Upload",
            html: true,
            text: `<div style="font-size:15px;">
                       ✅ Uploaded: <strong>${totalSuccess}</strong> files<br>
                       ❌ Failed: <strong>${totalFiles - totalSuccess}</strong> files<br>
                       ${errorSummaryHtml}
                       <div style="margin-top:10px; padding:10px; background:#fff3cd; border-radius:4px;">
                           <small><strong>💡 Tip:</strong> Check file formats, sizes, and try smaller batches</small>
                       </div>
                   </div>`,
            confirmButtonText: 'OK'
        });
    } else {
        swal({
            type: 'error',
            title: "Upload Failed",
            html: true,
            text: `<div style="font-size:15px;">
                       ❌ All files failed to upload<br>
                       ${errorSummaryHtml}
                       <div style="margin-top:10px; padding:10px; background:#f8d7da; border-radius:4px;">
                           <small><strong>⚠️ Common causes:</strong><br>
                           • Files corrupted or not real images<br>
                           • Server storage full<br>
                           • Permission issues<br>
                           • Network timeout
                           </small>
                       </div>
                   </div>`,
            confirmButtonText: 'OK'
        });
    }
    
    selectedFiles = [];
    renderFileList();
    
    if (totalSuccess > 0) {
        loadImageList(album_id);
        buildAlbum();
    }
});

// // js/photo.js ที่แก้ไขแล้ว
// function buildPhotoPage() {
//     // **✅ ดึง classroom_id จาก Hidden Field ในหน้าแม่**
//     var classroomId = $('#classroom_id').val(); 
//     var contentUrl = '/classroom/management/actions/photo.php';
    
//     $.ajax({
//         url: contentUrl,
//         type: 'POST', // **เปลี่ยนเป็น POST เพื่อส่ง classroom_id ได้ง่าย**
//         data: { classroom_id: classroomId }, // **✅ ส่ง classroom_id ไปด้วย**
//         dataType: 'html',
//         beforeSend: function() {
//             $(".content-container").html('<div class="text-center" style="padding: 50px;"><i class="fas fa-spinner fa-spin fa-2x"></i> Loading...</div>');
//         }
//     })
//     .done(function(data) {
//         $(".content-container").html(data);
        
//         // **✅ ตั้งค่า Hidden Field ในฟอร์มย่อย (photo.php) ให้ถูกต้องอีกครั้ง**
//         $('#form_classroom_id').val(classroomId); 
//     })
//     .fail(function() {
//         $(".content-container").html('<div class="alert alert-danger">ไม่สามารถโหลดเนื้อหา **เพิ่มรูปภาพกลุ่มสำหรับ Face Recognition** ได้</div>');
//     });
// }
