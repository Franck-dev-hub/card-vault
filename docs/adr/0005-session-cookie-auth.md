---
status: accepted
---

# Authenticate with a session cookie, not a JWT

Users log in to a session kept on the server, in Redis; the browser only holds
a cookie that points to it.\
Deleting the session on the server logs the user out at once, which password
reset, account deletion and bans rely on; a JWT would stay valid until it
expires.\
The frontend and the API share one domain (see
[0003](0003-internal-ml-single-origin.md)), so the cookie is sent with every
request without any cross-domain setup.
