let currentPage = 1;
let isLoading = false;
let hasMore = true;
let classroom_id = null;
$(document).ready(function () {
    classroom_id = $("#classroom_id").val() || null;
    if (!classroom_id) {
        nonePermissionAlert();
        return;
    }
    getPermission();
    bindScroll();
});
function bindScroll() {
    let scrollTimeout = null;
    $(window).on('scroll', function () {
        if (scrollTimeout) return;
        scrollTimeout = setTimeout(function () {
            scrollTimeout = null;
            if (!hasMore || isLoading || !classroom_id) return;
            if ($(window).scrollTop() + $(window).height() >
                $(document).height() - 600) {
                fetchPhotos();
            }
        }, 150);
    });
}
let image_count = 0;
function getPermission() {
    $.ajax({
        url: "/classroom/study/actions/myphoto_album.php",
        type: "POST",
        dataType: "JSON",
        data: {
            action: 'getPermission',
            classroom_id: classroom_id
        },
        success: function (result) {
            if (!result || !result.status) {
                nonePermissionAlert();
                return;
            }
            setupEvent(result.classroom_data);
            image_count = result.image_count;
            $(".images-counter").html(image_count);
            fetchPhotos();
        },
        error: function () {
            nonePermissionAlert();
        }
    });
}
function setupEvent(classroom_data) {
    classroom_id = classroom_data.classroom_id;
    $(".event-title").text(classroom_data.event_name || "-");
    // $(".event-location").text(classroom_data.event_location_name || "-");
    $(".event-time").text(
        (classroom_data.event_date || "") + " " + (classroom_data.event_time || "")
    );
    $(document).attr("title", classroom_data.event_name + " • ORIGAMI SYSTEM");
    if (!classroom_data.event_poster) return;
    const posterUrl = classroom_data.event_poster;
    const $poster = $('#posterPreload');
    $poster.off('load error');
    $poster.attr('src', posterUrl).on('load', function () {
        $('.event-header')[0].style.setProperty('--event-bg', `url("${posterUrl}")`);
    }).on('error', function () {
        console.warn('Poster load failed:', posterUrl);
    });
}
function fetchPhotos() {
    if (isLoading || !hasMore || !classroom_id) return;
    isLoading = true;
    $('#loading-area').show();
    $.getJSON('/classroom/study/actions/myphoto_album.php', {
        action: 'loadImage',
        page: currentPage,
        classroom_id: classroom_id
    }).done(function (data) {
        if (!Array.isArray(data) || data.length === 0) {
            hasMore = false;
            $('#loading-area').html(
                '<p style="color:#666; margin:20px;">--- End of Image ---</p>'
            );
            return;
        }
        renderPhotos(data);
        currentPage++;
    }).fail(function () {
        console.error('Load image failed');
    }).always(function () {
        isLoading = false;
    });
}
let allPhotos = [];
let currentIndex = 0;
function renderPhotos(photos) {
    let gridHtml = '';
    let thumbHtml = '';
    $.each(photos, function (_, photo) {
        allPhotos.push(photo);
        const index = allPhotos.length - 1;
        const thumb = escapeUrl(photo.thumb);
        gridHtml += `
            <div class="photo-item" data-index="${index}" onclick="openModalByIndex(${index})">
                <div class="img-container">
                    <img 
                        src="${thumb}" 
                        alt="Photo" 
                        loading="lazy"
                        onload="setPhotoOrientation(this)"
                    >
                </div>
            </div>
        `;
        thumbHtml += `
            <img 
                id="thumb-${index}" 
                src="${thumb}" 
                class="thumb-item new-item"
                loading="lazy"
                onload="setPhotoOrientation(this)"
                onclick="if(!isDragging) openModalByIndex(${index})"
            >
        `;
    });
    const $gridItems = $(gridHtml);
    $('#photo-grid').append($gridItems);
    setTimeout(() => $gridItems.addClass('loaded'), 60);
    const $thumbItems = $(thumbHtml);
    $('#thumbStrip').append($thumbItems);
    setTimeout(() => $thumbItems.removeClass('new-item'), 100);
}
function setPhotoOrientation(img) {
    const w = img.naturalWidth;
    const h = img.naturalHeight;
    if (!w || !h) return;
    const $wrapper = $(img).closest('.photo-item, .thumb-item, .img-container');
    $wrapper.removeClass('portrait landscape square');
    if (w > h) {
        $wrapper.addClass('landscape');
    } else if (h > w) {
        $wrapper.addClass('portrait');
    } else {
        $wrapper.addClass('square');
    }
}
let imageLoadToken = 0;
function openModalByIndex(index) {
    currentIndex = parseInt(index);
    currentRotation = 0;
    const photo = allPhotos[currentIndex];
    const myToken = ++imageLoadToken;
    $('#modalImg')
        .css('transform', 'translate(-50%, -50%) rotate(0deg)')
        .attr('src', photo.thumb)
        .css('filter', 'blur(0px)');
    $('.modal-loader').show();
    $('#downloadBtn').attr('href', photo.url);
    $('.index-counter').text(`${(currentIndex + 1).toLocaleString()}`);
    const newImg = new Image();
    newImg.src = photo.url;
    newImg.onload = function () {
        if (myToken !== imageLoadToken) return;
        $('.modal-loader').hide();
        $('#modalImg').attr('src', photo.url).css('filter', 'none').fadeIn(300);
        $('.index-counter').text(`${(currentIndex + 1).toLocaleString()}`);
    };
    updateThumbStrip();
    if ($('#previewModal').is(':hidden')) {
        $('#previewModal').fadeIn(300).css('display', 'flex');
        $('body').css('overflow', 'hidden');
    }
}
let currentRotation = 0;
function rotateImage(direction) {
    currentRotation += direction * 90;
    $('#modalImg').css({
        'transform': `translate(-50%, -50%) rotate(${currentRotation}deg)`,
        'transition': 'transform 0.3s ease'
    });
}
function navImage(direction, event) {
    if (event) event.stopPropagation();
    let nextIndex = currentIndex + direction;
    if (direction > 0 && nextIndex >= allPhotos.length - 5 && hasMore && !isLoading) {
        fetchPhotos();
    }
    if (nextIndex < 0) nextIndex = allPhotos.length - 1;
    if (nextIndex >= allPhotos.length) nextIndex = 0;
    openModalByIndex(nextIndex);
}
function updateThumbStrip() {
    $('#thumbStrip .thumb-item').removeClass('active');
    $(`#thumb-${currentIndex}`).addClass('active');
    const activeThumb = document.getElementById(`thumb-${currentIndex}`);
    const strip = document.getElementById('thumbStrip');
    if (activeThumb && strip) {
        const offsetLeft = activeThumb.offsetLeft - (strip.offsetWidth / 2) + (activeThumb.offsetWidth / 2);
        strip.scrollTo({
            left: offsetLeft,
            behavior: 'smooth'
        });
    }
}
const slider = document.querySelector('.thumb-strip');
let isDown = false;
let startX;
let scrollLeft;
let isDragging = false; 
const startDragging = (e) => {
    isDown = true;
    isDragging = false; 
    const pageX = e.pageX || e.touches[0].pageX;
    startX = pageX - slider.offsetLeft;
    scrollLeft = slider.scrollLeft;
};
const moveDragging = (e) => {
    if (!isDown) return;
    const pageX = e.pageX || (e.touches ? e.touches[0].pageX : null);
    if (!pageX) return;
    const x = pageX - slider.offsetLeft;
    const walk = (x - startX) * 2;
    if (Math.abs(x - startX) > 10) {
        isDragging = true;
        slider.scrollLeft = scrollLeft - walk;
        if (e.cancelable) e.preventDefault(); 
        const scrollEnd = slider.scrollWidth - slider.clientWidth;
        if (slider.scrollLeft >= scrollEnd - 300 && hasMore && !isLoading) {
            fetchPhotos();
        }
    }
};
const stopDragging = (e) => {
    isDown = false;
};
slider.addEventListener('click', (e) => {
    if (isDragging) {
        e.stopImmediatePropagation();
        e.preventDefault();
    }
}, true); 
slider.addEventListener('mousedown', startDragging);
slider.addEventListener('mousemove', moveDragging);
slider.addEventListener('mouseup', stopDragging);
slider.addEventListener('mouseleave', stopDragging);
slider.addEventListener('touchstart', startDragging, {passive: true});
slider.addEventListener('touchmove', moveDragging, {passive: false});
slider.addEventListener('touchend', stopDragging, {passive: true});
$(document).keydown(function(e) {
    if ($('#previewModal').is(':visible')) {
        if (e.keyCode == 37) navImage(-1);
        if (e.keyCode == 39) navImage(1);
        if (e.keyCode == 27) closeModal();
    }
});
function closeModal() {
    $('#previewModal').fadeOut(300);
    $('body').css('overflow', 'auto');
}
function escapeUrl(url) {
    return String(url).replace(/'/g, "%27");
}
function nonePermissionAlert() {
    swal({
        html: true,
        title: "Error",
        text: "An error occurred. Please try again later.",
        type: "error",
        confirmButtonText: "OK",
        confirmButtonColor: "#F27474"
    }, function () {
        window.location.reload();
    });
}
let touchstartX = 0;
let touchendX = 0;
function handleGesture() {
    const threshold = 50; 
    if (touchendX < touchstartX - threshold) {
        navImage(1);
    }
    if (touchendX > touchstartX + threshold) {
        navImage(-1);
    }
}
const modalElement = document.getElementById('previewModal');
modalElement.addEventListener('touchstart', e => {
    touchstartX = e.changedTouches[0].screenX;
}, {passive: true});
modalElement.addEventListener('touchend', e => {
    if (e.target.closest('.thumb-strip')) {
        return;
    }
    touchendX = e.changedTouches[0].screenX;
    handleGesture();
}, {passive: true});
$(document).ready(function () {
    classroom_id = $("#classroom_id").val() || null;
    if (!classroom_id) {
        nonePermissionAlert();
        return;
    }
    getPermission();
    bindScroll();
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    if (isIOS) {
        $('body').addClass('is-ios');
    }
    $(document).on('click', '#downloadBtn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const url = btn.attr('href');
        const fileName = `ORIGAMI_${new Date().getTime()}.jpg`;
        if (!url) return;
        const originalHtml = btn.html();
        btn.html('<i class="glyphicon glyphicon-refresh spin"></i> PREPARING...').addClass('disabled');
        fetch(url).then(response => response.blob()).then(blob => {
            const blobUrl = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = blobUrl;
            a.download = fileName;
            document.body.appendChild(a);
            a.click();
            setTimeout(() => {
                window.URL.revokeObjectURL(blobUrl);
                document.body.removeChild(a);
                btn.html(originalHtml).removeClass('disabled');
            }, 100);
        })
        .catch(() => {
            window.open(url, '_blank');
            btn.html(originalHtml).removeClass('disabled');
        });
    });
});
function closeGallery() {
    $(".loader").addClass("active");

    $.ajax({
		url: "/classroom/study/actions/myphoto_album.php",
		data: {
			action: 'closeGallery'
		},
		dataType: "JSON",
		type: 'POST',
		success: function(result){
            $(".loader").removeClass("active");
            if(result.status == true) {
                
                var classroom_id = result.classroom_id;
                window.location = "/classroom/study/myphoto";
            } else {
                nonePermissionAlert();
            }
        }
    }); 
}