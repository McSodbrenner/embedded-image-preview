# Embedded Image Preview (EIP)

Using the Embedded Image Preview (EIP) technology presented here, we can load qualitatively different preview images from progressive JPEGs, depending on the application purpose, with the help of Ajax and HTTP Range Requests. The data from these preview images is not discarded but reused to display the entire image.

For a complete description of how it works, please look here: https://smashingmagazine.com/...

You will find a running version here:  

 * http://embedded-image-preview.cerdmann.com/example1/
 * http://embedded-image-preview.cerdmann.com/example2/
 * http://embedded-image-preview.cerdmann.com/example3/
 * http://embedded-image-preview.cerdmann.com/prototype/

---

If you want to try it out yourself you need a PHP capable web server.
If you already have PHP installed use the `_server/server.bat` or `_server/server.sh` to start PHPs builtin development server.
Then point your browser to one of the following URLs:

 * http://localhost:8080/example1/
 * http://localhost:8080/example2/
 * http://localhost:8080/example3/
 * http://localhost:8080/prototype/

