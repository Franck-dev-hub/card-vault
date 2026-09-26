---
status: accepted
---

# Keep the ML service internal, serve everything from one origin

Only the API can reach the ML service: it has no public port, and the proxy
answers 404 on `/ml/*`.\
Every scan therefore goes through the API's authentication and rate limiting.\
The frontend and the API are served from the same domain, so the browser never
calls another domain and no CORS setup is needed.\
Serving the frontend from its own domain would mean adding CORS back, allowing
exactly that domain and the session cookie.
