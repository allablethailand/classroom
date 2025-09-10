$(document).ready(function() {
  let currentDate = new Date();
  const $currentDateSpan = $('#current-date');

  function formatDate(date) {
    const options = { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' };
    return date.toLocaleDateString('en-US', options);
  }

  function updateDateDisplay() {
    $currentDateSpan.text(formatDate(currentDate));

    console.log("formDate:", currentDate);

    const sqlDate = currentDate.toISOString().split('T')[0];
    console.log('Load schedule for:', currentDate.toISOString().split('T')[0]);

    const myData = {
            "2025-09-01": [
                {
                "event_location": "พัทยา/ชลบุรี",
                "event_start": "09:30",
                "event_end": "12:00",
                "session_detail": "ลงทะเบียนผู้เข้าอบรม, รายงานตัว, ตัดสูท, ถ่ายรูป, แจกเสื้อโปโล หมวก, ป้ายชื่อ, สแกน QR เข้ากลุ่ม 3 กลุ่ม, sign PDPA, สมุดโทรศัพท์",
                "session_speaker": null
                },
                {
                "event_location": "พัทยา/ชลบุรี",
                "event_start": "13:00",
                "event_end": "17:00",
                "session_detail": "พิธีเปิด ประธานกล่าวเปิดหลักสูตร, ผอ.หลักสูตร อธิบายรายละเอียดหลักสูตร, กิจกรรมละลายพฤติกรรม",
                "session_speaker": null
                },
                {
                "event_location": "พัทยา/ชลบุรี",
                "event_start": "18:00",
                "event_end": null,
                "session_detail": "แต่ละกลุ่มคุยเรื่องการแสดงโชว์ในช่วงกินเลี้ยง, กินเลี้ยง, แสดงโชว์แต่ละกลุ่ม (\"หลักสูตร เป็นเจ้าภาพจัดเลี้ยง\")",
                "session_speaker": null
                }
            ],
            "2025-09-02": [
                {
                "event_location": "พัทยา",
                "event_start": "9:30",
                "event_end": "12:00",
                "session_detail": "รับฟังการบรรยาย AI, หัวข้อ: Deep drive in AI",
                "session_speaker": "พี่กฤษ"
                },
                {
                "event_location": "พัทยา",
                "event_start": "13:00",
                "event_end": "16:00",
                "session_detail": "รับฟังการบรรยาย AI, หัวข้อ: Knowledge Base and Business AI in Organization",
                "session_speaker": "พี่กฤษ"
                }
            ],
            "2025-09-03": [
                {
                "event_location": "พัทยา",
                "event_start": "9:30",
                "event_end": "12:00",
                "session_detail": "รับฟังการบรรยาย, หัวข้อ: Green : Shift & Sustainability Landscape",
                "session_speaker": "พี่เบนซ์"
                },
                {
                "event_location": "พัทยา",
                "event_start": "13:00",
                "event_end": "16:30",
                "session_detail": "รับฟังการบรรยาย, หัวข้อ: กลยุทธ์และธรรมมาภิบาล ESG",
                "session_speaker": "พี่เบนซ์"
                },
                {
                "event_location": "พัทยา",
                "event_start": "18:00",
                "event_end": null,
                "session_detail": "กลุ่มดิน เป็นเจ้าภาพจัดเลี้ยง",
                "session_speaker": null
                }
            ],
            "2025-09-04": [
                {
                "event_location": "พัทยา",
                "event_start": "9:30",
                "event_end": "12:00",
                "session_detail": "รับฟังการบรรยาย AI, หัวข้อ: AWS Deep AI Technology",
                "session_speaker": "พี่กฤษ"
                },
                {
                "event_location": "พัทยา",
                "event_start": "13:00",
                "event_end": "16:30",
                "session_detail": "รับฟังการบรรยาย AI, หัวข้อ: Transform your organization by Huawei cloud",
                "session_speaker": "พี่กฤษ"
                },
                {
                "event_location": "พัทยา",
                "event_start": "18:00",
                "event_end": null,
                "session_detail": "กลุ่มน้ำ เป็นเจ้าภาพจัดเลี้ยง",
                "session_speaker": null
                }
            ],
            "2025-09-05": [
                {
                "event_location": "ดูงานต่างประเทศ, เซินเจิ้น ประเทศจีน",
                "event_start": "9:00",
                "event_end": "18:00",
                "session_detail": "เยี่ยมชมองค์กร และโครงการต้นแบบ",
                "session_speaker": null
                }
            ],
            "2025-09-06": [
                {
                "event_location": "ดูงานต่างประเทศ, เซินเจิ้น ประเทศจีน",
                "event_start": "9:00",
                "event_end": "18:00",
                "session_detail": "เยี่ยมชมองค์กร และโครงการต้นแบบ",
                "session_speaker": null
                }
            ],
            "2025-09-07": [
                {
                "event_location": "ดูงานต่างประเทศ, เซินเจิ้น ประเทศจีน",
                "event_start": "9:00",
                "event_end": "18:00",
                "session_detail": "เยี่ยมชมองค์กร และโครงการต้นแบบ",
                "session_speaker": null
                }
            ],
            "2025-09-08": [
                {
                "event_location": "ดูงานต่างประเทศ, เซินเจิ้น ประเทศจีน",
                "event_start": "9:00",
                "event_end": "18:00",
                "session_detail": "เยี่ยมชมองค์กร และโครงการต้นแบบ",
                "session_speaker": null
                }
            ],
            "2025-09-09": [
                {
                "event_location": "พัทยา",
                "event_start": "9:30",
                "event_end": "12:00",
                "session_detail": "รับฟังการบรรยาย, หัวข้อ: การเงินสีเขียว & ความเสี่ยงสภาพภูมิอากาศ",
                "session_speaker": "พี่เบนซ์"
                },
                {
                "event_location": "พัทยา",
                "event_start": "13:00",
                "event_end": "16:30",
                "session_detail": "รับฟังการบรรยาย, หัวข้อ: Green Innovation & Cirular Models",
                "session_speaker": "พี่เบนซ์"
                },
                {
                "event_location": "พัทยา",
                "event_start": "18:00",
                "event_end": null,
                "session_detail": "กลุ่มลม เป็นเจ้าภาพจัดเลี้ยง",
                "session_speaker": null
                }
            ],
            "2025-09-10": [
                {
                "event_location": "พัทยา",
                "event_start": "9:30",
                "event_end": "12:00",
                "session_detail": "รับฟังการบรรยาย AI, หัวข้อ: Digital Transformation by AI in Organization",
                "session_speaker": "พี่กฤษ"
                },
                {
                "event_location": "พัทยา",
                "event_start": "13:00",
                "event_end": "16:30",
                "session_detail": "รับฟังการบรรยาย AI, หัวข้อ: Organization Digital Technology",
                "session_speaker": "พี่กฤษ"
                },
                {
                "event_location": "พัทยา",
                "event_start": "18:00",
                "event_end": null,
                "session_detail": "กลุ่มไฟ เป็นเจ้าภาพจัดเลี้ยง",
                "session_speaker": null
                }
            ],
            "2025-09-11": [
                {
                "event_location": "พัทยา",
                "event_start": "9:30",
                "event_end": "12:00",
                "session_detail": "รับฟังการบรรยาย, หัวข้อ: Sector Deep Dive (เลือกตามกลุ่มเป้าหมาย)",
                "session_speaker": "พี่เบนซ์"
                },
                {
                "event_location": "พัทยา",
                "event_start": "13:00",
                "event_end": "16:30",
                "session_detail": "รับฟังการบรรยาย, หัวข้อ: ผู้นำ องค์กร และอนาคต",
                "session_speaker": "พี่เบนซ์"
                },
                {
                "event_location": "พัทยา",
                "event_start": "18:00",
                "event_end": null,
                "session_detail": "กลุ่มหลักสูตร เป็นเจ้าภาพจัดเลี้ยง",
                "session_speaker": null
                }
            ],
            "2025-09-12": [
                {
                "event_location": "พัทยา",
                "event_start": "9:30",
                "event_end": "12:00",
                "session_detail": "เยี่ยมชม โรงงาน",
                "session_speaker": null
                },
                {
                "event_location": "พัทยา",
                "event_start": "14:30",
                "event_end": "16:00",
                "session_detail": "เยี่ยมชม โรงงาน",
                "session_speaker": null
                }
            ],
            "2025-09-13": [
                {
                "event_location": "พัทยา",
                "event_start": "9:30",
                "event_end": "12:00",
                "session_detail": "รับฟังการบรรยาย, หัวข้อ: การพัฒนาอุตสหกรรมสู่สังคมคาร์บอนเครดิตต่ำ ในสถานประกอบการ",
                "session_speaker": "เจ้อัง"
                },
                {
                "event_location": "พัทยา",
                "event_start": "13:00",
                "event_end": "16:30",
                "session_detail": "รับฟังการบรรยาย, หัวข้อ: การส่งเสริมยกระดับมาตรฐานสถานประกอบการสู่อุตสาหกรรมสีเขียว",
                "session_speaker": "เจ้อัง"
                },
                {
                "event_location": "พัทยา",
                "event_start": "18:00",
                "event_end": null,
                "session_detail": "**กลุ่มดิน+น้ำ เป็นเจ้าภาพจัดเลี้ยง",
                "session_speaker": null
                }
            ],
            "2025-09-14": [
                {
                "event_location": "พัทยา",
                "event_start": "9:30",
                "event_end": "12:00",
                "session_detail": "รับฟังการบรรยาย, หัวข้อ: แนวการจัดการกากอุตสาหกรรมตามหลักกฎหมาย",
                "session_speaker": "เจ้อัง"
                },
                {
                "event_location": "พัทยา",
                "event_start": "13:00",
                "event_end": "16:30",
                "session_detail": "รับฟังการบรรยาย, หัวข้อ: โอกาสทองของอุตสาหกรรมกับพื้นที่ EEC",
                "session_speaker": "อ.จุฬา (เจ้อัง)"
                },
                {
                "event_location": "พัทยา",
                "event_start": "18:00",
                "event_end": null,
                "session_detail": "**กลุ่มลม+ไฟ เป็นเจ้าภาพจัดเลี้ยง",
                "session_speaker": null
                }
            ],
            "2025-09-15": [
                {
                "event_location": "พัทยา",
                "event_start": "9:30",
                "event_end": "16:00",
                "session_detail": "รับฟังการบรรยาย, หัวข้อ: แนวการจัดการกากอุตสาหกรรมตามหลักกฎหมาย",
                "session_speaker": "เจ้อัง"
                },
                {
                "event_location": "พัทยา",
                "event_start": "13:00",
                "event_end": "16:30",
                "session_detail": "รับฟังการบรรยาย, หัวข้อ: โอกาสทองของอุตสาหกรรมกับพื้นที่ EEC",
                "session_speaker": "อ.จุฬา (เจ้อัง)"
                },
                {
                "event_location": "พัทยา",
                "event_start": "18:00",
                "event_end": null,
                "session_detail": "**กลุ่มลม+ไฟ เป็นเจ้าภาพจัดเลี้ยง",
                "session_speaker": null
                }
            ]
    }

    const sessions = myData[sqlDate] || [];

    if (sessions.length === 0) {
        $('.featured-class').hide();  // Hide if empty
    } else {
        $('.featured-class').show();  // Show if not empty (data rewound or changed)
    }

    // Send the session data to PHP backend to generate HTML
    $.ajax({
        url: '/classroom/study/actions/schedule.php', // your PHP file
        type: 'POST',
        contentType: 'application/json',      // send JSON
        data: JSON.stringify({
            action: 'fetch_mydata',
            sessions: sessions,
            date: sqlDate
        }),
        success: function(html) {
            // On success, inject returned HTML in your container
            $('#scheduleContainer').html(html);
        },
        error: function(xhr, status, error) {
            console.error('Failed to load schedule:', error);
            $('#scheduleContainer').html('<p class="text-center">ไม่พบข้อมูลวันดังกล่าว</p>');
        }
        });
    }

  

    // Fetch Data Schedule
    // $.ajax({
    //     url: "/classroom/study/actions/schedule.php",
    //     type:"GET",
    //     data: {
    //         action : 'fetch_schedules',
    //         date_range: sqlDate
    //     },
    //     dataType: "JSON",
    //     success: function(result){
    //         if(result.status == true) {
    //             swal({type: 'success',title: "Successfully",text: "", showConfirmButton: false,timer: 1500});
    //             load_table();
    //         } else {
    //             swal({type: 'warning',title: "Warning...",text: 'An authorization error occurred. Please proceed again.'});
    //         }
    //     }
    // });


  $('#prev-day').on('click', function() {
    currentDate.setDate(currentDate.getDate() - 1);
    updateDateDisplay();
  });

  $('#next-day').on('click', function() {
    currentDate.setDate(currentDate.getDate() + 1);
    updateDateDisplay();
  });

  // Cancel First Modal


    
  updateDateDisplay();
});
