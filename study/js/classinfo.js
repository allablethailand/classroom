document.addEventListener("DOMContentLoaded", function () {
  var timestamp = {
    timestatus: null,
    loading: false,
    timeserver_origin: null,
    timeserver: null,
    timestamp_update: null,
  };
  var usergeo = { };
  var useragent = { device: null, os: null, browser: null };

  const is_browser_support = true;

  const timestampButton = document.getElementById("timestamp-button");
  const timestampImg = document.getElementById("timestamp-img");
  const stampPhotoInput = document.getElementById("stamp_photo");
  const timeServerDiv = document.getElementById("time-server");

  function updateUI() {
    if (!is_browser_support) {
      timestampButton.classList.add("center-box");
    } else {
      timestampButton.classList.remove("center-box");
    }

    if (timestamp.timestatus === "out") {
      timestampButton.style.pointerEvents = "none";
      timestampButton.style.cursor = "default";
    } else {
      timestampButton.style.pointerEvents = null;
      timestampButton.style.cursor = "pointer";
    }

    timeServerDiv.textContent = timestamp.timeserver || "";

    if (!timestamp.timestatus) {
      timestampImg.src = "images/stamp_in_button.png";
      timestampImg.style.display = "";
      timestampImg.alt = "Stamp In Button";
    } else if (timestamp.timestatus === "in") {
      timestampImg.src = "images/stamp_out_button.png";
      timestampImg.style.display = "";
      timestampImg.alt = "Stamp Out Button";
    } else if (timestamp.timestatus === "out") {
      timestampImg.src = "images/stamp_disable_button.png";
      timestampImg.style.display = "";
      timestampImg.alt = "Stamp Disabled Button";
    } else {
      timestampImg.style.display = "none";
      if (!document.getElementById("unsupported-span")) {
        const span = document.createElement("span");
        span.id = "unsupported-span";
        span.textContent = "Sorry your browser does not support";
        timestampButton.appendChild(span);
      }
      return;
    }

    const unsupportedSpan = document.getElementById("unsupported-span");
    if (unsupportedSpan) {
      unsupportedSpan.remove();
    }
  }

  function updateClock() {
     if (timestamp.timestamp_update) {
    clearInterval(timestamp.timestamp_update);
  }
  if (timestamp.timeserver_origin) {
    timestamp.timeserver_origin = new Date(timestamp.timeserver_origin.getTime() + 1000);
    timestamp.timeserver = timestamp.timeserver_origin.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
    updateUI();
  }
  timestamp.timestamp_update = setInterval(() => {
    if (timestamp.timeserver_origin) {
      timestamp.timeserver_origin = new Date(timestamp.timeserver_origin.getTime() + 1000);
      timestamp.timeserver = timestamp.timeserver_origin.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
      updateUI();
    }
  }, 1000);
  }

  async function initializeUserData() {
    if (typeof UAParser === "function") {
      var parser = new UAParser();
      useragent = parser.getResult();

      if (useragent.device && useragent.device.type === "mobile") {
        document.body.classList.add("is-device");
        var browser = useragent.browser;
        if (browser) {
          var browserName = browser.name ? browser.name.toLowerCase() : "";
          if (browserName !== "line" && browserName !== "facebook") {
            if (navigator.geolocation) {
              try {
                await new Promise((resolve, reject) => {
                  navigator.geolocation.getCurrentPosition(
                    (position) => {
                      usergeo.lat = position.coords.latitude;
                      usergeo.lng = position.coords.longitude;
                      usergeo.accuracy = position.coords.accuracy;
                      resolve();
                    },
                    (error) => reject(error),
                    { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
                  );
                });
                // Fetch initial stamp time from server here if needed...
                // Example:
                /*
                              const response = await fetchData("_");
                              if (response && response.stamptime) {
                                  const s = response.stamptime;
                                  timestamp.timeserver_origin = new Date(s.timeserver);
                                  timestamp.timeserver = timestamp.timeserver_origin.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
                                  timestamp.timestatus = s.status || null;
                                  updateClock();
                              }
                              */
              } catch (err) {
                console.warn("Geolocation error:", err);
              }
            }
          }
        }
      }
    }
  }

  function resizeImage(file, maxSize) {
    // var file = settings.file;
    var maxSize = file.maxSize;

    // console.log(settings);
    console.log(file);
    //     console.log("file type", file.type);

    return new Promise((resolve, reject) => {
      if (!file.type.match(/image.*/)) {
        reject(new Error("Not an image"));
        return;
      }

      var reader = new FileReader();
      var image = new Image();
      var canvas = document.createElement("canvas");

      function dataURItoBlob(dataURI) {
        const byteString =
          dataURI.split(",")[0].indexOf("base64") >= 0
            ? atob(dataURI.split(",")[1])
            : decodeURI(dataURI.split(",")[1]);
        const mimeString = dataURI.split(",")[0].split(":")[1].split(";")[0];
        const arrayBuffer = new Uint8Array(byteString.length);
        for (let i = 0; i < byteString.length; i++) {
          arrayBuffer[i] = byteString.charCodeAt(i);
        }
        return new Blob([arrayBuffer], { type: mimeString });
      }

      function resize() {
        let width = image.width;
        let height = image.height;

        if (width > height) {
          if (width > maxSize) {
            height *= maxSize / width;
            width = maxSize;
          }
        } else {
          if (height > maxSize) {
            width *= maxSize / height;
            height = maxSize;
          }
        }

        canvas.width = width;
        canvas.height = height;

        const ctx = canvas.getContext("2d");
        ctx.clearRect(0, 0, width, height);
        ctx.drawImage(image, 0, 0, width, height);
        const dataUrl = canvas.toDataURL("image/jpeg");
        return dataURItoBlob(dataUrl);
      }

      reader.onload = function (e) {
        image.onload = function () {
          resolve(resize());
        };
        image.src = e.target.result;
      };

      reader.onerror = reject;
      reader.readAsDataURL(file);
    });
  }

  function storeBlob(blob) {
    return new Promise((resolve, reject) => {
      var reader = new FileReader();
      reader.onloadend = () => resolve(reader.result);
      reader.onerror = reject;
      reader.readAsDataURL(blob);
    });
  }

  function getOrientation(file, callback) {
    var reader = new FileReader();
    reader.onload = function (e) {
      var view = new DataView(e.target.result);
      if (view.getUint16(0, false) != 0xffd8) {
        return callback(-2);
      }
      var length = view.byteLength,
        offset = 2;
      while (offset < length) {
        if (view.getUint16(offset + 2, false) <= 8) return callback(-1);
        var marker = view.getUint16(offset, false);
        offset += 2;
        if (marker == 0xffe1) {
          if (view.getUint32((offset += 2), false) != 0x45786966) {
            return callback(-1);
          }
          var little = view.getUint16((offset += 6), false) == 0x4949;
          offset += view.getUint32(offset + 4, little);
          var tags = view.getUint16(offset, little);
          offset += 2;
          for (var i = 0; i < tags; i++) {
            if (view.getUint16(offset + i * 12, little) == 0x0112) {
              return callback(view.getUint16(offset + i * 12 + 8, little));
            }
          }
        } else if ((marker & 0xff00) != 0xff00) {
          break;
        } else {
          offset += view.getUint16(offset, false);
        }
      }
      return callback(-1);
    };
    reader.readAsArrayBuffer(file);
  }

  async function onStampPhoto(e) {
    var files = e.target.files;
    if (files && files.length > 0) {
      var file = files[0];

      try {
        const resizedImageBlob = await resizeImage(file, 300);
        const photoDataUrl = await storeBlob(resizedImageBlob);
        const photoExif = await new Promise((resolve) => {
          getOrientation(file, (result) => {
            resolve(result === -2 ? 8 : result);
          });
        });
        // Call stampTime with photo data
        console.log("usergeo:", usergeo);
        stampTime({ photo: photoDataUrl, photo_exif: photoExif });
      } catch (err) {
        alert("ON STAMP ERROR!");
        console.error("Error processing photo:", err);
      }
    }
    forceUpdate();
  }

  function openTimeStamp() {
    if (timestamp.timestatus !== "out") {
      var message =
        timestamp.timestatus !== "in"
          ? "พร้อมที่จะเข้าเรียนแล้วใช่มั้ย?"
          : "ต้องการ check out ออก class แล้วใช่มั้ย?";
      if (confirm(message)) {
        var stampPhoto = document.getElementById("stamp_photo");
        if (stampPhoto) {
          stampPhoto.click();
        }
      }
    }
  }

  function forceUpdate() {
    updateUI();
  }

  function fetchData(actions, data = {}, endpoint, method) {
    return fetch(endpoint || "/classroom/study/actions/classinfo.php", {
      method: method || "POST",
      mode: "cors",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        action: actions,
        data: data,
      }),
    })
      .then(async (response) => {
        var result = await response.json();
        return result;
      })
      .catch((err) => {});
  }

  async function stampTime(stampPhotoData) {
    if (!usergeo.lat || !usergeo.lng) {
      alert("Your location can't be found");
      return;
    }
    timestamp.loading = true;

    const payload = {
      lat: usergeo.lat,
      lng: usergeo.lng,
      device: useragent.device ? useragent.device.type : null,
      os: useragent.os ? useragent.os.name : null,
      browser: useragent.browser ? useragent.browser.name : null,
      stamp_status: timestamp.timestatus,
      stamp_photo: stampPhotoData,
    };
    try {
      // console.log("HELLO");
      const response = await fetchData("stamptime", payload);
      console.log(response);
      if (response && response.status) {
        timestamp.timestatus = response.stampstatus;
        timestamp.timeserver_origin = new Date(response.timeserver);
        timestamp.timeserver = timestamp.timeserver_origin.toLocaleTimeString(
          [],
          { hour: "2-digit", minute: "2-digit" }
        );
        updateClock();
        alert("You already stamp time");
        location.reload();
      } else {
        alert("Can't stamp");
      }
    } catch (err) {
      console.error(err);
      alert("Error during stamping");
    } finally {
      timestamp.loading = false;
    }
  }

  (async () => {
    await initializeUserData();
    updateUI(); 

    if (usergeo.lat && usergeo.lng) {
    timestampButton.disabled = false;
    } else {
      timestampButton.disabled = true;
    }
  })();

  timestampButton.addEventListener("click", openTimeStamp);
  stampPhotoInput.addEventListener("change", onStampPhoto);
});
