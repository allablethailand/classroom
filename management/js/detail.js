let classroom_id;
let management_template;
let join_template;
let tb_staff;
let table_join_user;
function mapInit(lat,lng) {
	lat = (lat) ? lat : '13.736717';
	lng = (lng) ? lng : '100.523186';
	var user_location = [lat,lng];
    user_location = new google.maps.LatLng(user_location[0],user_location[1]);
    var map = new google.maps.Map(document.querySelector('.map-container'), {
        center: user_location,
        zoom: 10
    });
    gmarkers = new google.maps.Marker({
        position: user_location,
        map: map,
    });
    var input = document.createElement("input");
    input.setAttribute("type", "text");
    input.setAttribute("id", "pac-input");
    input.setAttribute("class", "controls form-control");
    input.setAttribute("style", "width:400px;margin-top: 13px;");
    var searchBox = new google.maps.places.SearchBox(input);
    map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
    map.addListener('bounds_changed', function() {
        searchBox.setBounds(map.getBounds());
    });
    google.maps.event.addListener(map, 'click', function(e) {
        var lat = e.latLng.lat();
        var lng = e.latLng.lng();
        var myLatLng = {
            lat,
            lng
        };
        if (gmarkers) {
            gmarkers.setMap(null);
        }
        var marker = new google.maps.Marker({
            position: myLatLng,
            map: map,
        });
        gmarkers = marker;
        document.querySelector('#location-lat').value = lat;
        document.querySelector('#location-lng').value = lng;
        if(lat && lng){
            last_location = `${lat},${lng}`;
        }
    });
    searchBox.addListener('places_changed', function() {
        var places = searchBox.getPlaces();
        if (places.length == 0) {
            return;
        }
        var bounds = new google.maps.LatLngBounds();
        places.forEach(function(place) {
            if (!place.geometry) {
                console.log("Returned place contains no geometry");
                return;
            }
            var icon = {
                url: place.icon,
                size: new google.maps.Size(71, 71),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(17, 34),
                scaledSize: new google.maps.Size(25, 25)
            };
            if (place.geometry.viewport) {
                bounds.union(place.geometry.viewport);
            } else {
                bounds.extend(place.geometry.location);
            }
        });
        map.fitBounds(bounds);
		var lng = bounds.Fh.hi;
		var lat = bounds.ci.hi;
		var myLatLng = {
            lat,
            lng
        };
        if (gmarkers) {
            gmarkers.setMap(null);
        }
        var marker = new google.maps.Marker({
            position: myLatLng,
            map: map,
        });
		gmarkers = marker;
        document.querySelector('#location-lat').value = lat;
        document.querySelector('#location-lng').value = lng;
        if(lat && lng){
            last_location = `${lat},${lng}`;
        }
    });
} 
$(document).ready(function() {
    initializeClassroomManagement();
    $('.getLatLong').click(function(e) {
		var lng = $(this).attr("lng");
		var lat = $(this).attr("lat");
        $('.locationModal').modal();
        $("#location-lat").val(lat);
        $("#location-lng").val(lng);
		mapInit(lat,lng);
    });
    $(".btn-save-location").click(function(){
        var lat = $("#location-lat").val();
        var lng = $("#location-lng").val();
		if(lat && lng) {
			var val = lat+','+lng;
			$(".getLatLong").val(val);
			$('.locationModal').modal("hide");
		} else {
			swal({type: 'warning',title: "Warning...",text: 'Please select a location.',showConfirmButton: false,timer: 3000});
		}
    });
});
function initializeClassroomManagement() {
    classroom_id = $("#classroom_id").val();
    if(classroom_id) {
        $(".edit-mode").removeClass("hidden");
    } else {
        $(".edit-mode").addClass("hidden");
    }
    $(".get-management").on("click", function() {
        $(".get-management").removeClass("active");
        $(this).addClass("active");
        const page = $(this).attr("data-page");
        if (page) {
            buildPage(page);
        }
    });
    buildPage('management');
}
function buildPage(page) {
    switch(page) {
        case 'management':
            buildManagementPage();
            break;
        case 'course':
            buildCoursePage();
            break;
        case 'group':
            buildGroupPage();
            break;
        case 'registration':
            buildRegistrationPage();
            break;
        case 'consent':
            buildConsentPage();
            break;
        case 'student':
            buildStudentPage();
            break;
        case 'teacher':
            buildTeacherPage();
            break;
        case 'email':
            buildEmailPage();
            break;
        default:
            console.warn('Unknown page type:', page);
    }
}
function buildManagementPage() {
    $(".content-container").html(getManagementTemplate());
    initializeDropify();
    initializeDateTimePickers();
    initializeEditor();
    buildManagementData();
    buildRegisterTemplate();
    buildStaff();
    bindFormEventHandlers();
    buildLineOA();
    buildPlatform();
}
function initializeDropify() {
    try {
        $('.dropify').dropify({
            tpl: {
                wrap: '<div class="dropify-wrapper"></div>',
                loader: '<div class="dropify-loader"></div>',
                message: '<div class="dropify-message"><span class="file-icon" /> <p>{{ default }}</p></div>',
                preview: '<div class="dropify-preview"><span class="dropify-render"></span><div class="dropify-infos"><div class="dropify-infos-inner"><p class="dropify-infos-message">{{ replace }}</p></div></div></div>',
                filename: '<p class="dropify-filename"><span class="file-icon"></span> <span class="dropify-filename-inner"></span></p>',
                clearButton: '<div><button type="button" class="dropify-clear dropify-view">Preview</button><button type="button" class="dropify-clear">{{ remove }}</button></div>',
                errorLine: '<p class="dropify-error">{{ error }}</p>',
                errorsContainer: '<div class="dropify-errors-container"><ul></ul></div>'
            }
        });
        $('.dropify-view').on('click', function(event) {
            event.stopPropagation();
            event.preventDefault();
            const img = $(this).closest('.dropify-wrapper').find('.dropify-render img').attr('src');
            if (img && $.fancybox) {
                $.fancybox({ href: img });
            }
        });
        $('.dropify').on('dropify.beforeClear', function(event, element) {
            $('#ex_classroom_poster').val('');
        });
    } catch (error) {
        console.error('Error initializing Dropify:', error);
    }
}
function initializeDateTimePickers() {
    try {
        $('.clockpicker').clockpicker({
            autoclose: true
        });
        $('.datepicker').datepicker({
            dateFormat: 'yy/mm/dd', 
            changeMonth: true,
            changeYear: true,
            autoclose: true
        });
    } catch (error) {
        console.error('Error initializing date/time pickers:', error);
    }
}
function initializeEditor() {
    try {
        const editorElement = $('#classroom_information');
        if (editorElement.length && typeof editorElement.editable === 'function') {
            editorElement.editable({
                theme: 'gray',
                inlineMode: false,
                buttons: [
                    'bold', 'italic', 'underline', 'strikeThrough', 'subscript', 'superscript',
                    'fontFamily', 'fontSize', 'color', 'formatBlock', 'blockStyle', 'inlineStyle',
                    'align', 'insertOrderedList', 'insertUnorderedList', 'outdent', 'indent',
                    'selectAll', 'createLink', 'insertImage', 'table', 'undo', 'redo',
                    'insertHorizontalRule', 'uploadFile', 'fullscreen', 'html'
                ],
                minHeight: 450,
                imageUploadURL: 'upload_image.php',
                imageDeleteURL: 'delete_image.php'
            });
            $("a[href='http://editor.froala.com']").parent().remove();
        }
        const editorElement2 = $('#contact_us');
        if (editorElement2.length && typeof editorElement2.editable === 'function') {
            editorElement2.editable({
                theme: 'gray',
                inlineMode: false,
                buttons: [
                    'bold', 'italic', 'underline', 'strikeThrough'
                ],
                minHeight: 150,
            });
            $("a[href='http://editor.froala.com']").parent().remove();
        }
    } catch (error) {
        console.error('Error initializing editor:', error);
    }
}
function bindFormEventHandlers() {
    $('input[name="auto_password"]').on('change', function() {
        const isEnabled = $(this).val() === '0';
        $('#auto_pwd_settings').toggle(isEnabled);
    });
    $('input[name="password_type"]').on('change', function() {
        const isCustom = $(this).val() === 'custom';
        $('#custom_pattern_block').toggle(isCustom);
        $('#random_pattern_block').toggle(!isCustom);
    });
    $('#auto_password_length').on('input', function() {
        $('#length_display').text($(this).val());
    });
    $('#auto_username_length').on('input', function() {
        $('#username_length_display').text($(this).val());
    });
    $('input[name="auto_username"]').on('change', function() {
        const isEnabled = $(this).val() === '0';
        $('#auto_uname_settings').toggle(isEnabled);
    });
    $('input[name="line_oa"]').on('change', function() {
        const isEnabled = $(this).val() === '0';
        $('.for-lineconnect').toggleClass('hidden', !isEnabled);
    });
    $('input[name="classroom_allow_register"]').on('change', function() {
        const isEnabled = $(this).val() === '0';
        $('.for-open-register').toggleClass('hidden', !isEnabled);
    });
    $('input[name="classroom_type"]').on('change', function() {
        const isOnline = $(this).val() === 'online';
        $('.for-online').toggleClass('hidden', !isOnline);
        $('.for-onsite').toggleClass('hidden', isOnline);
    });
}
function buildRegisterTemplate() {
    $.ajax({
        url: "/classroom/management/actions/detail.php",
        type: "POST",
        data: { 
            action: 'buildRegisterTemplate', 
            classroom_id: classroom_id 
        },
        dataType: "JSON",
        success: function(result) {
            if (result && result.template_data) {
                populateFormRegister(result.template_data);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading management data:', error);
        }
    });
}
function populateFormRegister(template_data) {
    let tbody = $("#tb_register_template tbody");
    tbody.empty();
    template_data.forEach(function(t) {
        let chkDisplay = `<input type="checkbox" class="chk-display" data-id="${t.template_id}" name="register_template[]" ${t.template_display == 0 ? 'checked' : ''} ${t.is_default == 0 ? 'disabled' : ''} value="${t.template_id}">`;
        let chkRequire = `<input type="checkbox" class="chk-require" data-id="${t.template_id}" name="register_require[]" ${t.template_require == 0 ? 'checked' : ''} ${t.is_default == 0 ? 'disabled' : ''} value="${t.template_id}">`;
        let row = `
            <tr>
                <td class="text-center">${chkDisplay}</td>
                <td class="text-center">${chkRequire}</td>
                <td>${t.template_name_en}</td>
                <td>${t.template_name_th}</td>
            </tr>
        `;
        tbody.append(row);
    });
}
function buildManagementData() {
    if (!classroom_id) return;
    $.ajax({
        url: "/classroom/management/actions/detail.php",
        type: "POST",
        data: { 
            action: 'buildMenagementData', 
            classroom_id: classroom_id 
        },
        dataType: "JSON",
        success: function(result) {
            if (result && result.classroom_data) {
                populateFormData(result.classroom_data);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading management data:', error);
        }
    });
}
function populateFormData(data) {
    try {
        if (data.classroom_poster) {
            $("#classroom_poster").attr("data-default-file", data.classroom_poster);
            $("#ex_classroom_poster").val(data.classroom_poster);
        }
        $("#classroom_name").val(data.classroom_name || '');
        $("#classroom_student").val(data.classroom_student || '0');
        setDatePickerValue("#classroom_start_date", data.classroom_start_date);
        setDatePickerValue("#classroom_end_date", data.classroom_end_date);
        $("#classroom_start_time").val(data.classroom_start_time || '');
        $("#classroom_end_time").val(data.classroom_end_time || '');
        setRadioValue("classroom_type", data.classroom_type);
        setRadioValue("classroom_allow_register", data.classroom_allow_register);
        setRadioValue("line_oa", data.line_oa);
        setRadioValue("auto_approve", data.auto_approve);
        setRadioValue("auto_username", data.auto_username);
        setRadioValue("auto_password", data.auto_password);
        const isEnabled = data.classroom_allow_register === '0';
        $('.for-open-register').toggleClass('hidden', !isEnabled);
        const isEnabledLineOA = data.line_oa === '0';
        $('.for-lineconnect').toggleClass('hidden', !isEnabledLineOA);
        const isOnline = data.classroom_type === 'online';
        $('.for-online').toggleClass('hidden', !isOnline);
        $('.for-onsite').toggleClass('hidden', isOnline);
        if (data.classroom_type === 'online' && data.platforms_id) {
            $('#classroom_plateform').append($('<option>', { 
                value: data.platforms_id, 
                text: data.platforms_name 
            }));
        } else if (data.platforms_name) {
            $(".getLatLong").val(data.platforms_name);
            const [lat, lng] = data.platforms_name.split(",");
            $(".getLatLong").attr({ lat, lng });
        }
        $("#classroom_source").val(data.classroom_source || '');
        setDatePickerValue("#classroom_open_register_date", data.classroom_open_register_date);
        setDatePickerValue("#classroom_close_register_date", data.classroom_close_register_date);
        $("#classroom_open_register_time").val(data.classroom_open_register_time || '');
        $("#classroom_close_register_time").val(data.classroom_close_register_time || '');
        $("#close_register_message").val(data.close_register_message || '');
        if (data.line_oa_name) {
            $('#line_oa_link').append($('<option>', { 
                value: data.line_oa_id, 
                text: data.line_oa_name 
            }));
        }
        if (data.classroom_information) {
            $('#classroom_information').editable("setHTML", data.classroom_information, true);
        }
        if (data.contact_us) {
            $('#contact_us').editable("setHTML", data.contact_us, true);
        }
        if (data.auto_username === '0') {
            $("#auto_uname_settings").show();
            setCheckboxArray('auto_username_type[]', data.auto_username_type);
            $('#auto_username_length').val(data.auto_username_length || 8);
            $('#username_length_display').text(data.auto_username_length || 8);
        }
        if (data.auto_password === '0') {
            $("#auto_pwd_settings").show();
            setRadioValue("password_type", data.password_type);
            if (data.password_type === 'custom') {
                $("#custom_pattern_block").show();
                $("#auto_password_custom").val(data.auto_password_custom || '');
            } else {
                $("#random_pattern_block").show();
                setCheckboxArray('auto_password_type[]', data.auto_password_type);
                $('#auto_password_length').val(data.auto_password_length || 8);
                $('#length_display').text(data.auto_password_length || 8);
            }
        }
        if (data.case_sensitivity === 'Y') {
            $("#password_sensitivity_case").prop("checked", true);
        }
        $("#emp_group").val(data.staff_groups);
    } catch (error) {
        console.error('Error populating form data:', error);
    }
}
function setRadioValue(name, value) {
    if (value !== undefined && value !== null) {
        $(`input[name="${name}"][value="${value}"]`).prop("checked", true);
    }
}
function setCheckboxArray(name, values) {
    if (values) {
        const valueArray = String(values).split(",");
        valueArray.forEach(function(value) {
            $(`input[name="${name}"][value="${value.trim()}"]`).prop('checked', true);
        });
    }
}
function setDatePickerValue(selector, date) {
    if (date) {
        $(selector).datepicker('setDate', date);
    }
}
function buildStaff() {
    const tableSelector = '#tb_staff';
    if ($.fn.DataTable.isDataTable(tableSelector)) {
        $(tableSelector).DataTable().ajax.reload(null, false);
        return;
    }
    try {
        tb_staff = $(tableSelector).DataTable({
            processing: true,
            lengthMenu: [[50, 100, 250, 500, 1000, -1], [50, 100, 250, 500, 1000, "All"]],
            ajax: {
                url: "/classroom/management/actions/detail.php",
                type: "POST",
                data: function(data) {
                    data.action = "buildStaff";
                    data.classroom_id = classroom_id;
                    data.emp_group = $("#emp_group").val();
                },
                error: function(xhr, error, thrown) {
                    console.error('Error loading staff data:', error);
                }
            },
            language: typeof default_language !== 'undefined' ? default_language : {},
            responsive: true,
            searchDelay: 1000,
            deferRender: false,
            drawCallback: function(settings) {
                initializeLanguage();
            },
            createdRow: function(row, data, dataIndex, meta) {
                $(row).addClass('target' + dataIndex + ' target-tr');
            },
            order: [[2, 'asc']],
            columns: getStaffTableColumns()
        });
        setupStaffTableSearch();
    } catch (error) {
        console.error('Error initializing staff table:', error);
    }
}
function getStaffTableColumns() {
    return [{ 
        targets: 0,
        data: "emp_id",
        visible: false,
        render: function(data) { return data || ''; }
    },{ 
        targets: 1,
        data: "emp_pic",
        className: "text-center",
        render: function(data) {
            const src = data || '/images/default.png';
            return `<div class="avatar-sm"><img src="${src}" onerror="this.src='/images/default.png'"></div>`;
        }
    },{ 
        targets: 2,
        data: "emp_code",
        render: function(data) { return data || ''; }
    },{ 
        targets: 3,
        data: "emp_name",
        render: function(data) { return data || ''; }
    },{ 
        targets: 4,
        data: "posi_description",
        render: function(data) { return data || ''; }
    },{ 
        targets: 5,
        data: "dept_description",
        render: function(data) { return data || ''; }
    },{ 
        targets: 6,
        data: "emp_id",
        className: "text-center",
        render: function(data, type, row, meta) {
            const empId = row["emp_id"];
            const rowIndex = meta.row + meta.settings._iDisplayStart;
            return `
                <input type="hidden" name="emp_id[]" value="${empId}">
                <button type="button" class="btn btn-circle btn-red" onclick="delStaff(${rowIndex})">
                    <i class="fas fa-trash-alt"></i>
                </button>
            `;
        }
    }];
}
function setupStaffTableSearch() {
    const filterDiv = 'div#tb_staff_filter.dataTables_filter';
    $(`${filterDiv} label input, ${filterDiv} label span`).remove();
    const searchTemplate = `
        <input type="search" class="form-control search-datatable" placeholder="Search..." autocomplete="off" style="height:31px;"> 
        <button type="button" class="btn btn-green" lang="en" onclick="joinStaff();" style="font-size:12px;">Join Staff</button>
    `;
    $(`${filterDiv} input`).hide();
    $(`${filterDiv} label`).append(searchTemplate);
    const searchDataTable = $.fn.dataTable.util.throttle(function(val) {
        if (typeof val !== 'undefined') {
            tb_staff.search(val).draw();
        }
    }, 1000);
    $('.search-datatable').on('keyup', function(e) {
        if (e.keyCode === 13) {
            $('.dataTables_processing.panel').css('top', '5%');
            const val = e.target.value.trim().replace(/ /g, "");
            searchDataTable(val);
        }
        if (e.target.value === '') {
            tb_staff.search('').draw();
            buildStaff();
        }
    });
}
function initializeLanguage() {
    try {
        if (typeof Lang !== 'undefined') {
            const lang = new Lang();
            lang.dynamic('th', '/js/langpack/th.json?v=' + Date.now());
            lang.init({ defaultLang: 'en' });
        }
    } catch (error) {
        console.error('Error initializing language:', error);
    }
}
function delStaff(item) {
    if (tb_staff) {
        tb_staff.row(".target" + item).remove().draw();
    }
}
function joinStaff() {
    const headerHtml = `
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title"><i class="fas fa-users"></i> <span lang="en">Join Staff</span></h5>
    `;
    const footerHtml = `
        <button type="button" class="btn btn-orange" lang="en" onclick="saveJoin();">Join</button> 
        <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
    `;
    $(".systemModal").modal();
    $(".systemModal .modal-header").html(headerHtml);
    $(".systemModal .modal-footer").html(footerHtml);
    $(".systemModal .modal-body").html(getJoinTemplate());
    $("#emp_select").val($("#emp_group").val());
    buildDepartment();
    buildTableJoin();
}
function buildDepartment() {
    try {
        $("#dept_search").select2({
            theme: "bootstrap",
            placeholder: "Choose department",
            minimumInputLength: -1,
            allowClear: true,
            ajax: {
                url: "/classroom/management/actions/detail.php",
                dataType: 'json',
                delay: 250,
                cache: false,
                data: function(params) {
                    return {
                        term: params.term,
                        page: params.page || 1,
                        action: 'buildDepartment'
                    };
                },
                processResults: function(data, params) {
                    const page = params.page || 1;
                    return {
                        results: $.map(data, function(item) {
                            return {
                                id: item.id,
                                text: item.col,
                                code: item.code,
                                desc: item.desc,
                            };
                        }),
                        pagination: {
                            more: (page * 10) <= (data[0] ? data[0].total_count : 0)
                        }
                    };
                },
            },
            templateSelection: function(data) {
                return data.text;
            },
        });
    } catch (error) {
        console.error('Error building department dropdown:', error);
    }
}
function buildLineOA() {
    try {
        $("#line_oa_link").select2({
            theme: "bootstrap",
            placeholder: "Choose Line OA",
            minimumInputLength: -1,
            allowClear: true,
            ajax: {
                url: "/classroom/management/actions/detail.php",
                dataType: 'json',
                delay: 250,
                cache: false,
                data: function(params) {
                    return {
                        term: params.term,
                        page: params.page || 1,
                        action: 'buildLineOA'
                    };
                },
                processResults: function(data, params) {
                    const page = params.page || 1;
                    return {
                        results: $.map(data, function(item) {
                            return {
                                id: item.id,
                                text: item.col,
                                code: item.code,
                                desc: item.desc,
                            };
                        }),
                        pagination: {
                            more: (page * 10) <= (data[0] ? data[0].total_count : 0)
                        }
                    };
                },
            },
            templateSelection: function(data) {
                return data.text;
            },
        });
    } catch (error) {
        console.error('Error building department dropdown:', error);
    }
}
function buildPlatform() {
    try {
        $("#classroom_plateform").select2({
            theme: "bootstrap",
            placeholder: "Choose Platform",
            minimumInputLength: -1,
            allowClear: true,
            ajax: {
                url: "/classroom/management/actions/detail.php",
                dataType: 'json',
                delay: 250,
                cache: false,
                data: function(params) {
                    return {
                        term: params.term,
                        page: params.page || 1,
                        action: 'buildPlatform'
                    };
                },
                processResults: function(data, params) {
                    const page = params.page || 1;
                    return {
                        results: $.map(data, function(item) {
                            return {
                                id: item.id,
                                text: item.col,
                                code: item.code,
                                desc: item.desc,
                            };
                        }),
                        pagination: {
                            more: (page * 10) <= (data[0] ? data[0].total_count : 0)
                        }
                    };
                },
            },
            templateSelection: function(data) {
                return data.text;
            },
        });
    } catch (error) {
        console.error('Error building department dropdown:', error);
    }
}
function buildTableJoin() {
    const tableSelector = '#table_join_user';
    if ($.fn.DataTable.isDataTable(tableSelector)) {
        $(tableSelector).DataTable().ajax.reload(null, false);
        return;
    }
    try {
        table_join_user = $(tableSelector).DataTable({
            processing: true,
            lengthMenu: [[50, 100, 250, 500, 1000, -1], [50, 100, 250, 500, 1000, "All"]],
            ajax: {
                url: "/classroom/management/actions/detail.php",
                type: "POST",
                data: function(data) {
                    data.action = "buildTableJoin";
                    data.emp_select = $("#emp_select").val();
                },
                error: function(xhr, error, thrown) {
                    console.error('Error loading join table data:', error);
                }
            },
            language: typeof default_language !== 'undefined' ? default_language : {},
            responsive: true,
            searchDelay: 1000,
            deferRender: false,
            createdRow: function(row, data, dataIndex, meta) {
                $(row).addClass('target' + dataIndex + ' target-tr');
            },
            drawCallback: function(settings) {
                initializeLanguage();
            },
            order: [[2, 'asc']],
            columns: getJoinTableColumns()
        });
        setupJoinTableSearch();
    } catch (error) {
        console.error('Error initializing join table:', error);
    }
}
function getJoinTableColumns() {
    return [{ 
        targets: 0,
        data: "emp_id",
        visible: false
    },{ 
        targets: 1,
        data: "emp_pic",
        className: "text-center",
        render: function(data) {
            return `<div class="avatar-sm"><img src="${data}" onerror="this.src='/images/default.png'"></div>`;
        }
    },{ 
        targets: 2, data: "emp_code" 
    },{ 
        targets: 3, data: "emp_name" 
    },{ 
        targets: 4, data: "posi_description" 
    },{ 
        targets: 5, data: "dept_description" 
    },{ 
        targets: 6,
        data: "emp_id",
        className: "text-center",
        render: function(data, type, row, meta) {
            const rowIndex = meta.row + meta.settings._iDisplayStart;
            return `<button type="button" class="btn btn-circle btn-red" onclick="removeJoin(${rowIndex},${data})"><i class="fas fa-trash-alt"></i></button>`;
        }
    }];
}
function setupJoinTableSearch() {
    const filterDiv = 'div#table_join_user_filter.dataTables_filter';
    $(`${filterDiv} label input, ${filterDiv} label span`).remove();
    const template = `<input type="search" class="form-control search-datatable" placeholder="" autocomplete="off">`;
    $(`${filterDiv} input`).hide();
    $(`${filterDiv} label`).append(template);
    const searchDataTable = $.fn.dataTable.util.throttle(function(val) {
        if (typeof val !== 'undefined') {
            table_join_user.search(val).draw();
        }
    }, 1000);
    $('.search-datatable').on('keyup', function(e) {
        if (e.keyCode === 13) {
            $('.dataTables_processing.panel').css('top', '5%');
            const val = e.target.value.trim().replace(/ /g, "");
            searchDataTable(val);
        }
        if (e.target.value === '') {
            table_join_user.search('').draw();
            buildTableJoin();
        }
    });
}
function removeJoin(item, empId) {
    if (table_join_user) {
        table_join_user.row(".target" + item).remove().draw();
    }
    const empSelect = $("#emp_select").val();
    let dataArr = empSelect ? empSelect.split(',') : [];
    dataArr = dataArr.filter(value => value != empId);
    $("#emp_select").val(dataArr.join(','));
}
function saveJoin() {
    $("#emp_group").val($("#emp_select").val());
    buildStaff();
    $(".systemModal").modal("hide");
}
function buildUI() {
    const keyword = $(".search_data").val().trim();
    const dropdown = $(".dropdown-list-user");
    if (!keyword) {
        dropdown.hide().empty();
        return;
    }
    $.ajax({
        url: '/classroom/management/actions/detail.php',
        type: "POST",
        data: {
            action: 'buildUI',
            keyword: keyword,
            emp_select: $("#emp_select").val(),
            dept_search: $("#dept_search").val()
        },
        dataType: "JSON",
        success: function(result) {
            dropdown.empty().show();
            if (!result || result.length === 0) {
                dropdown.append('<li class="empty">No data available</li>');
                return;
            }
            const listItems = result.map(emp => createEmployeeListItem(emp)).join('');
            dropdown.append(listItems);
        },
        error: function(xhr, status, error) {
            console.error('Error building UI:', error);
            dropdown.hide();
        }
    });
}
function createEmployeeListItem(emp) {
    const empId = emp.emp_id || 0;
    const empName = emp.emp_name || '-';
    const empCode = emp.emp_code || '-';
    const deptDesc = emp.dept_description || '-';
    const posiDesc = emp.posi_description || '-';
    const empPic = emp.emp_pic || '/images/default.png';
    return `
        <li style="cursor:pointer;" onclick="selectToJoin(${empId});">
            <div class="row">
                <div class="col-xs-3">
                    <div class="img-avatar">
                        <img src="${empPic}" onerror="this.src='/images/default.png'">
                    </div>
                </div>
                <div class="col-xs-7">
                    <div style="margin-bottom:15px;">${empName}</div>
                    <div style="font-size:10px;"><i class="fas fa-address-card"></i> ${empCode}</div>
                    <div style="font-size:10px; color:#888888;"><i class="fas fa-building"></i> ${deptDesc}</div>
                    <div style="font-size:10px; color:#888888;"><i class="fas fa-briefcase"></i> ${posiDesc}</div>
                </div>
                <div class="col-xs-2 text-right">
                    <button class="btn btn-circle btn-white"><i class="fas fa-check"></i></button>
                </div>
            </div>
        </li>
    `;
}
function selectToJoin(empId) {
    const empSelect = $("#emp_select").val();
    let dataArr = empSelect ? empSelect.split(',') : [];
    if (!dataArr.includes(String(empId))) {
        dataArr.push(empId);
        $("#emp_select").val(dataArr.join(','));
        buildTableJoin();
    }
}
function buildUIGroup() {
    $.ajax({
        url: '/classroom/management/actions/detail.php',
        type: "POST",
        data: {
            action: 'buildUIGroup',
            dept_search: $("#dept_search").val(),
            emp_select: $("#emp_select").val()
        },
        dataType: "JSON",
        success: function(result) {
            if (result && result.length > 0) {
                const empSelect = $("#emp_select").val();
                let dataArr = empSelect ? empSelect.split(',') : [];
                result.forEach(emp => {
                    if (!dataArr.includes(String(emp.emp_id))) {
                        dataArr.push(emp.emp_id);
                    }
                });
                $("#emp_select").val(dataArr.join(','));
                buildTableJoin();
            }
        },
        error: function(xhr, status, error) {
            console.error('Error building UI group:', error);
        }
    });
}
function handleClickOutside() {
    document.onclick = function(e) {
        const target = e.target;
        if (target.id !== 'divToHide' && target.id !== 'divToHidetxt') {
            $("#divToHide").hide();
            $(".search_data").val("");
        }
    };
}
function getManagementTemplate() {
    return `
        <form id="form_classroom">
            <input type="hidden" name="classroom_id" value="${classroom_id}">
            <div class="form-group row">							
                <div class="col-sm-12">
                    <div class="form-group">
                        <div class="cal-sm-12 text-left">
                            <span class="label label-head bg-head-first">1</span>
                            <b lang="en">Information</b>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-6">
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label lang="en" class="control-label" for="classroom_poster">Logo</label>
                        </div>
                        <div class="col-sm-9">
                            <input name="classroom_poster" id="classroom_poster" type="file" class="dropify" data-allowed-file-extensions='["jpg", "jpeg", "png"]' data-max-file-size="25M" data-height="155" data-default-file="">
                            <input name="ex_classroom_poster" id="ex_classroom_poster" type="hidden">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label lang="en" class="control-label required-field" for="classroom_name">Academy Name</label>
                        </div>
                        <div class="col-sm-9">
                            <input type="text" class="form-control require_obj" id="classroom_name" name="classroom_name" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label lang="en" class="control-label required-field">Start</label>
                        </div>
                        <div class="col-sm-9">
                            <div class="row">
                                <div class="col-xs-7">
                                    <div class="input-group">
                                        <input type="text" class="datepicker form-control require_obj"  name="classroom_start_date" id="classroom_start_date" autocomplete="off" required onchange="getDate('event');">
                                        <span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
                                    </div>
                                </div>
                                <div class="col-xs-5">
                                    <div class="input-group">
                                        <input type="text" class="clockpicker form-control require_obj" name="classroom_start_time" id="classroom_start_time" autocomplete="off" required onchange="getDate('event');">
                                        <span class="input-group-addon"><i class="far fa-clock"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label lang="en" class="control-label required-field">End</label>
                        </div>
                        <div class="col-sm-9">
                            <div class="row">
                                <div class="col-xs-7">
                                    <div class="input-group">
                                        <input type="text" class="datepicker form-control require_obj" name="classroom_end_date" id="classroom_end_date" autocomplete="off" required onchange="getDate('event');">
                                        <span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
                                    </div>
                                </div>
                                <div class="col-xs-5">
                                    <div class="input-group">
                                        <input type="text" class="clockpicker form-control require_obj" name="classroom_end_time" id="classroom_end_time" autocomplete="off" required onchange="getDate('event');">
                                        <span class="input-group-addon"><i class="far fa-clock"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label lang="en" class="control-label required-field">Mode</label>
                        </div>
                        <div class="col-sm-9">
                            <div class="checkbox checkbox-success">
                                <input class="styled" id="classroom_type_online" name="classroom_type" type="radio" value="online" checked>
                                <label for="classroom_type_online" lang="en">Online</label>
                            </div>
                            <div class="checkbox checkbox-danger">
                                <input class="styled" id="classroom_type_onsite" name="classroom_type" type="radio" value="onsite">
                                <label for="classroom_type_onsite" lang="en">Onsite</label>
                            </div>
                        </div>
                    </div>
                    <div class="for-online">
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label lang="en" class="control-label">Platform</label>
                            </div>
                            <div class="col-sm-9">
                                <select name="classroom_plateform" id="classroom_plateform" class="form-control"></select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label lang="en" class="control-label">Link</label>
                            </div>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="classroom_source" id="classroom_source" autocomplete="off">
                                    <span class="input-group-addon"><i class="fas fa-link"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="for-onsite hidden">
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label lang="en" class="control-label">Location Place</label>
                            </div>
                            <div class="col-sm-9">
                                <div class="input-group getLatLong" style="cursor:pointer;">
                                    <input type="text" class="form-control getLatLong" name="classroom_plateform" id="classroom_plateform" autocomplete="off" placeholder="Latitude,Longitude">
                                    <span class="input-group-addon"><i class="fas fa-map-marker-alt"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label lang="en" class="control-label">Location Name</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="classroom_source" id="classroom_source" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label lang="en" class="control-label required-field">Number of Students</label>
                        </div>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <input type="text" class="form-control require_obj" name="classroom_student" id="classroom_student" autocomplete="off" required style="text-align:right;" min="0" value="0" onkeypress="return isNumberKeyEvent(event);" onClick="this.select();">
                                <span class="input-group-addon"><i class="fas fa-users"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label lang="en" class="control-label required-field">Registration Open</label>
                        </div>
                        <div class="col-sm-9">
                            <div class="checkbox checkbox-success">
                                <input class="styled" id="classroom_allow_register_0" name="classroom_allow_register" type="radio" value="0" checked>
                                <label for="classroom_allow_register_0" lang="en">Yes</label>
                            </div>
                            <div class="checkbox checkbox-danger">
                                <input class="styled" id="classroom_allow_register_1" name="classroom_allow_register" type="radio" value="1">
                                <label for="classroom_allow_register_1" lang="en">No</label>
                            </div>
                        </div>
                    </div>
                    <div class="for-open-register">
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label lang="en" class="control-label required-field">Registration Start</label>
                            </div>
                            <div class="col-sm-9">
                                <div class="row">
                                    <div class="col-xs-7">
                                        <div class="input-group">
                                            <input type="text" class="datepicker form-control require_obj" name="classroom_open_register_date" id="classroom_open_register_date" autocomplete="off" onchange="getDate('register');">
                                            <span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
                                        </div>
                                    </div>
                                    <div class="col-xs-5">
                                        <div class="input-group">
                                            <input type="text" class="clockpicker form-control require_obj" name="classroom_open_register_time" id="classroom_open_register_time" autocomplete="off" onchange="getDate('register');">
                                            <span class="input-group-addon"><i class="far fa-clock"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label lang="en" class="control-label required-field">Registration End</label>
                            </div>
                            <div class="col-sm-9">
                                <div class="row">
                                    <div class="col-xs-7">
                                        <div class="input-group">
                                            <input type="text" class="datepicker form-control require_obj" name="classroom_close_register_date" id="classroom_close_register_date" autocomplete="off" onchange="getDate('register');">
                                            <span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
                                        </div>
                                    </div>
                                    <div class="col-xs-5">
                                        <div class="input-group">
                                            <input type="text" class="clockpicker form-control require_obj" name="classroom_close_register_time" id="classroom_close_register_time" autocomplete="off" onchange="getDate('register');">
                                            <span class="input-group-addon"><i class="far fa-clock"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label lang="en" class="control-label">Close Registration Message</label>
                            </div>
                            <div class="col-sm-9">
                                <textarea name="close_register_message" id="close_register_message" class="form-control" style="height:100px;"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label lang="en" class="control-label required-field">Line Connect</label>
                        </div>
                        <div class="col-sm-9">
                            <div class="checkbox checkbox-success">
                                <input class="styled" id="line_oa_0" name="line_oa" type="radio" value="0">
                                <label for="line_oa_0" lang="en">Yes</label>
                            </div>
                            <div class="checkbox checkbox-danger">
                                <input class="styled" id="line_oa_1" name="line_oa" type="radio" value="1" checked>
                                <label for="line_oa_1" lang="en">No</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row for-lineconnect hidden">
                        <div class="col-sm-3">
                            <label lang="en" class="control-label required-field">Line OA</label>
                        </div>
                        <div class="col-sm-9">
                            <select class="form-control require_obj" name="line_oa_link" id="line_oa_link"></select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label lang="en" class="control-label">Contact Us</label>
                        </div>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="contact_us" name="contact_us"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">							
                <div class="col-sm-12">
                    <div class="form-group">
                        <div class="cal-sm-12 text-left">
                            <span class="label label-head bg-head-first">2</span>
                            <b lang="en">Approval &amp; Security</b>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-6">
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label lang="en" class="control-label">Auto Approve</label>
                        </div>
                        <div class="col-sm-9">
                            <div class="checkbox checkbox-success">
                                <input class="styled" id="auto_approve_0" name="auto_approve" type="radio" value="0">
                                <label for="auto_approve_0" lang="en">Yes</label>
                            </div>
                            <div class="checkbox checkbox-danger">
                                <input class="styled" id="auto_approve_1" name="auto_approve" type="radio" value="1" checked>
                                <label for="auto_approve_1" lang="en">No</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label class="control-label"><span lang="en">Auto Username</span></label>
                        </div>
                        <div class="col-sm-9">
                            <div class="checkbox checkbox-success">
                                <input class="styled" id="auto_username_0" name="auto_username" type="radio" value="0">
                                <label for="auto_username_0" lang="en">Yes</label>
                            </div>
                            <div class="checkbox checkbox-danger">
                                <input class="styled" id="auto_username_1" name="auto_username" type="radio" value="1" checked>
                                <label for="auto_username_1" lang="en">No</label>
                            </div>
                        </div>
                    </div>
                    <div id="auto_uname_settings" style="display:none; margin-top:10px;">
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label class="control-label"><span lang="en">When email/phone exists</span></label>
                            </div>
                            <div class="col-sm-9">
                                <span class="form-control-static text-orange" style="margin-left:16px;">
                                    <b><i class="fas fa-info"></i> Use phone or email if available.</b>
                                </span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label class="control-label"><span lang="en">Duplicate? → Generate:</span></label>
                            </div>
                            <div class="col-sm-9">
                                <div class="checkbox checkbox-info" style="margin-left:16px;">
                                    <input class="styled" id="auto_username_type1" name="auto_username_type[]" type="checkbox" value="1" checked>
                                    <label for="auto_username_type1" lang="en">0-9</label>
                                </div>
                                <div class="checkbox checkbox-info">
                                    <input class="styled" id="auto_username_type2" name="auto_username_type[]" type="checkbox" value="2">
                                    <label for="auto_username_type2" lang="en">A–Z</label>
                                </div>
                                <div class="checkbox checkbox-info">
                                    <input class="styled" id="auto_username_type3" name="auto_username_type[]" type="checkbox" value="3">
                                    <label for="auto_username_type3" lang="en">a-z</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label lang="en" class="control-label">Username Length</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="range" class="form-control-range" name="auto_username_length" id="auto_username_length" min="4" max="20" value="8" oninput="$('#username_length_display').text(this.value)">
                                <small class="text-orange">Username length: <span id="username_length_display">8</span> characters</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label lang="en" class="control-label">Auto Password</label>
                        </div>
                        <div class="col-sm-9">
                            <div class="checkbox checkbox-success">
                                <input class="styled" id="auto_password_0" name="auto_password" type="radio" value="0">
                                <label for="auto_password_0" lang="en">Yes</label>
                            </div>
                            <div class="checkbox checkbox-danger">
                                <input class="styled" id="auto_password_1" name="auto_password" type="radio" value="1" checked>
                                <label for="auto_password_1" lang="en">No</label>
                            </div>
                        </div>
                    </div>
                    <div id="auto_pwd_settings" style="display:none; margin-top:10px;">
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label lang="en" class="control-label">Password Type</label>
                            </div>
                            <div class="col-sm-9">
                                <div class="checkbox checkbox-warning">
                                    <input class="styled" id="password_type_custom" name="password_type" type="radio" value="custom" checked>
                                    <label for="password_type_custom" lang="en">Custom</label>
                                </div>
                                <div class="checkbox checkbox-warning">
                                    <input class="styled" id="password_type_random" name="password_type" type="radio" value="random">
                                    <label for="password_type_random" lang="en">Random</label>
                                </div>
                            </div>
                        </div>
                        <div id="custom_pattern_block">
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label lang="en" class="control-label">Default Password</label>
                                </div>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="auto_password_custom" name="auto_password_custom" placeholder="e.g. GUEST-XXXX">
                                        <span class="input-group-addon"><i class="fas fa-unlock-alt"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="random_pattern_block" style="display:none;">
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label lang="en" class="control-label">Include Characters</label>
                                </div>
                                <div class="col-sm-9">
                                    <div class="checkbox checkbox-info" style="margin-left:16px;">
                                        <input class="styled" id="auto_password_type1" name="auto_password_type[]" type="checkbox" value="1" checked>
                                        <label for="auto_password_type1" lang="en">0-9</label>
                                    </div>
                                    <div class="checkbox checkbox-info">
                                        <input class="styled" id="auto_password_type2" name="auto_password_type[]" type="checkbox" value="2">
                                        <label for="auto_password_type2" lang="en">A–Z</label>
                                    </div>
                                    <div class="checkbox checkbox-info">
                                        <input class="styled" id="auto_password_type3" name="auto_password_type[]" type="checkbox" value="3">
                                        <label for="auto_password_type3" lang="en">a-z</label>
                                    </div>
                                    <div class="checkbox checkbox-info">
                                        <input class="styled" id="auto_password_type4" name="auto_password_type[]" type="checkbox" value="4">
                                        <label for="auto_password_type4" lang="en">Special (!@#$%)</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label lang="en" class="control-label">Password Length</label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="range" class="form-control-range" name="auto_password_length" id="auto_password_length" 
                                           min="4" max="20" value="8" oninput="$('#length_display').text(this.value)">
                                    <small class="text-orange">Password length: <span id="length_display">8</span> characters</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label lang="en" class="control-label">&nbsp;</label>
                        </div>
                        <div class="col-sm-9">
                            <div class="checkbox checkbox-danger" style="margin-left:16px;">
                                <input class="styled" id="password_sensitivity_case" name="password_sensitivity_case" type="checkbox" value="1" checked>
                                <label for="password_sensitivity_case" lang="en">Disable case sensitivity</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">							
                <div class="col-sm-12">
                    <div class="form-group">
                        <div class="cal-sm-12 text-left">
                            <span class="label label-head bg-head-first">3</span>
                            <b lang="en">Information</b>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">							
                <div class="col-sm-12">
                    <div class="form-group row">
                        <div class="col-sm-2">&nbsp;</div>
                        <div class="col-sm-10">
                            <textarea name="classroom_information" id="classroom_information" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">							
                <div class="col-sm-12">
                    <div class="form-group">
                        <div class="cal-sm-12 text-left">
                            <span class="label label-head bg-head-first">4</span>
                            <b lang="en">Register Template</b>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">							
                <div class="col-sm-12">
                    <div class="form-group row">
                        <div class="col-sm-2">&nbsp;</div>
                        <div class="col-sm-10">
                            <table class="table table-border" id="tb_register_template">
                                <thead>
                                    <tr>
                                        <th lang="en" class="text-center">Display Filed</th>
                                        <th lang="en" class="text-center">Require Filed</th>
                                        <th lang="en">Field name (EN)</th>
                                        <th lang="en">Field name (TH)</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">							
                <div class="col-sm-12">
                    <div class="form-group">
                        <div class="cal-sm-12 text-left">
                            <span class="label label-head bg-head-first">5</span>
                            <b lang="en">Join Staff</b>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">							
                <div class="col-sm-12">
                    <div class="form-group row">
                        <div class="col-sm-2">&nbsp;</div>
                        <div class="col-sm-10">
                            <input type="hidden" id="emp_group" name="emp_group">
                            <table class="table table-border" id="tb_staff">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th lang="en">Code</th>
                                        <th lang="en">Name</th>
                                        <th lang="en">Position</th>
                                        <th lang="en">Department</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-right">
                <button type="button" class="btn btn-orange btn-save" lang="en" onclick="saveManagement();">Save</button> 
                <button type="button" class="btn" lang="en" onclick="closeManagement();">Cancel</button>
            </div>
        </form>
    `;
}
function getJoinTemplate() {
    return `
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group row">
                    <div class="col-sm-2">
                        <label lang="en" class="control-label">Department</label>
                    </div>
                    <div class="col-sm-10">
                        <select class="form-control" id="dept_search" onchange="buildUIGroup();"></select>
                    </div>
                </div>
                <input type="hidden" id="emp_select">
                <div class="form-group row">
                    <div class="col-sm-2">
                        <label lang="en" class="control-label">Search</label>
                    </div>
                    <div class="col-sm-10">
                        <input type="text" id="divToHidetxt" class="form-control search_data" onkeyup="buildUI();" placeholder="Search..." style="display:block !important;">
                        <ul class="dropdown-menu dropdown-list-user" aria-labelledby="divToHide" id="divToHide"></ul>
                    </div>
                </div>
            </div>
        </div>
        <hr style="margin:10px auto;">
        <div class="row">
            <div class="col-sm-12">
                <table class="table" id="table_join_user">
                    <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th lang="en">Code</th>
                            <th lang="en">Name</th>
                            <th lang="en">Position</th>
                            <th lang="en">Department</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    `;
}
window.addEventListener('load', handleClickOutside);
function closeManagement() {
    event.stopPropagation();
    swal({
        html:true,
        title: window.lang.translate("Are you sure?"),
        text: 'Do you want to cancel this action?',
        type: "warning",
        showCancelButton: true,
        closeOnConfirm: false,
        confirmButtonText: window.lang.translate("Ok"),
        cancelButtonText: window.lang.translate("Cancel"),	
        confirmButtonColor: '#FBC02D',
        cancelButtonColor: '#CCCCCC',
        showLoaderOnConfirm: true,
    },
    function(isConfirm){
        if (isConfirm) {
			window.open('', '_self', ''); 
			window.close();
		}
	});
}
function parseDate(d, t) {
    return new Date(d + " " + t);
}
function getDate(type) {
    if (type === 'event') {
        let eStartD = document.getElementById("classroom_start_date").value;
        let eStartT = document.getElementById("classroom_start_time").value;
        let eEndD   = document.getElementById("classroom_end_date").value;
        let eEndT   = document.getElementById("classroom_end_time").value;
        if (eStartD && eStartT) {
            let start = parseDate(eStartD, eStartT);
            if (!eEndD || !eEndT) {
                document.getElementById("classroom_end_date").value = eStartD;
                document.getElementById("classroom_end_time").value = eStartT;
                return;
            }
            let end = parseDate(eEndD, eEndT);
            if (end < start) {
                document.getElementById("classroom_end_date").value = eStartD;
                document.getElementById("classroom_end_time").value = eStartT;
            }
        }
    }
    if (type === 'register') {
        let rStartD = document.getElementById("classroom_open_register_date").value;
        let rStartT = document.getElementById("classroom_open_register_time").value;
        let rEndD   = document.getElementById("classroom_close_register_date").value;
        let rEndT   = document.getElementById("classroom_close_register_time").value;
        if (rStartD && rStartT) {
            let start = parseDate(rStartD, rStartT);
            if (!rEndD || !rEndT) {
                document.getElementById("classroom_close_register_date").value = rStartD;
                document.getElementById("classroom_close_register_time").value = rStartT;
                return;
            }
            let end = parseDate(rEndD, rEndT);
            if (end < start) {
                document.getElementById("classroom_close_register_date").value = rStartD;
                document.getElementById("classroom_close_register_time").value = rStartT;
            }
        }
    }
}
function saveManagement() {
    var err = 0;
    $.each($(".require_obj"), function(){    
        if($(this).is(':visible') && !$(this).val()) {
            ++err;
        }              
    });
	if(err > 0) {
		swal({
			type: 'warning',
			title: "Warning",
			text: "Please input all item completely.",
			timer: 2500,
			showConfirmButton: false,
			allowOutsideClick: true
		});
		return;
	}
    $(".loader").addClass("active");
    var fd = new FormData();
    $("#form_classroom").find("input, select, textarea").each(function() {
        var $element = $(this);
        if ($element.is(':visible')) {
            var name = $element.attr('name');
            var type = $element.attr('type');
            if (name) {
                if (type === 'file') {
                    var files = $element[0].files;
                    if (files.length > 0) {
                        fd.append(name, files[0]);
                    }
                } else if (type === 'checkbox' || type === 'radio') {
                    if ($element.is(':checked')) {
                        fd.append(name, $element.val());
                    }
                } else {
                    fd.append(name, $element.val() || '');
                }
            }
        }
    });
    if (classroom_id) {
        fd.append('classroom_id', classroom_id);
    }
    fd.append('emp_group', $("#emp_group").val());
    fd.append('classroom_information', $("#classroom_information").val());
    fd.append('contact_us', $("#contact_us").val());
    $.ajax({
		url: "/classroom/management/actions/detail.php?action=saveManagement",
		type: "POST",
		data: fd,
		processData: false,
		contentType: false,
		dataType: "JSON",
		success: function(result){
			$(".loader").removeClass("active");
            if(result.status == false) {
                swal({type: 'warning', title: "Something went wrong", text: (result.message) ? result.message : "Please try again later", showConfirmButton: false, timer: 1500});
                return;
            }
			swal({type: 'success', title: "Successfully", text: "", showConfirmButton: false, timer: 1500});
			localStorage.removeItem('reloadManagement');
			localStorage.setItem('reloadManagement', 'true');
			if(!classroom_id) {
				setTimeout(function() {
					$.redirect("detail",{classroom_id: result.classroom_id}, 'post');
				}, 1500);
			}
		}
	});
}
function handleResponse(response) {
    if (response.status === 'redirect') {
        swal({
            type: 'warning',
            title: 'Something went wrong',
            text: response.message,
            confirmButtonColor: '#FF9900'
		},function(isConfirm){
			if (isConfirm) {
				window.location.href = response.redirect_url;
			}
		});
		return;
    } else if(response.status == false) {
		swal({
            type: 'warning',
            title: 'Something went wrong',
            text: response.message,
            confirmButtonColor: '#FF9900'
        },function(isConfirm){
			if (isConfirm) {
				swal.close();
			}
		});
		return;
	}
}