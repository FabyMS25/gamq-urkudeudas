# Map WebSocket notifications

This service receives backend publish requests and broadcasts them to external frontends.

Local usage:

```bash
npm install
npm run ws
```

Environment variables:

- `WS_HOST`: default `0.0.0.0`
- `WS_PORT`: default `8082`
- `WS_PATH`: default `/ws-notificaciones`
- `WS_PUBLISH_PATH`: default `/publish`
- `WS_PUBLISH_SECRET`: optional secret required in the backend publish request header

Frontend URL example:

```js
wsBackendUrl: 'ws://localhost:8082/ws-notificaciones'
```

Backend publish URL example:

```env
WS_PUBLISH_URL=http://127.0.0.1:8082/publish
```
