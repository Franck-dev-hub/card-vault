# Keep the ML service internal, serve everything from one origin

The ML service publishes no port and the public proxy answers 404 on `/ml/*`; only the API calls it, on the Docker network.\
The frontend and the API share one origin behind Caddy, so the browser never makes a cross-origin call and there is no CORS layer.\
Moving the frontend to its own origin would mean adding CORS back, with an exact origin and credentials for the session cookie.
