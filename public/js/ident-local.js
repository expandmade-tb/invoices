function client_ID() {
    var str = '';

    let s = str.concat(
        to_s(screen.height),
        to_s(screen.width),
        to_s(screen.colorDepth),
        to_s(screen.pixelDepth),
        to_s(navigator.language),
        to_s(navigator.userAgent),
        to_s(new Date().getTimezoneOffset()),
        to_s(navigator.buildID),
        to_s(navigator.deviceMemory),
        to_s(window.navigator.hardwareConcurrency),
        to_s(navigator.maxTouchPoints),
        to_s(navigator.vendor),
        to_s(navigator.webdriver),
        to_s(canvas_info())
    );

    setCookie("__Client-ID", hash(s), 1)
}

function setCookie(cname, cvalue, exdays) {
  const d = new Date();
  d.setTime(d.getTime() + (exdays*24*60*60*1000));
  let expires = "expires="+ d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function to_s(value) {
  if (value)
    return value.toString();
  else
    return "";
}

function canvas_info () {
    var canvas = document.createElement('canvas');
    var gl;
    var debugInfo;
    var vendor="";
    var renderer="";
    
    try {
      gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
    } catch (e) {
    }
    
    if (gl) {
      debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
      vendor = gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL);
      renderer = gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL);
    }
    
    return vendor.concat(renderer);
}

function hash (str) {
    var i = str.length
    var hash1 = 5381
    var hash2 = 52711
  
    while (i--) {
      const char = str.charCodeAt(i)
      hash1 = (hash1 * 33) ^ char
      hash2 = (hash2 * 33) ^ char
    }
  
    return (hash1 >>> 0) * 4096 + (hash2 >>> 0)
}