const http = require('http');
const { WebSocket, WebSocketServer } = require('ws');

const host = process.env.WS_HOST || '0.0.0.0';
const port = Number(process.env.WS_PORT || 8082);
const wsPath = process.env.WS_PATH || '/ws-notificaciones';
const publishPath = process.env.WS_PUBLISH_PATH || '/publish';
const secret = process.env.WS_PUBLISH_SECRET || '';

const clients = new Set();

function json(res, status, payload) {
  const body = JSON.stringify(payload);
  res.writeHead(status, {
    'content-type': 'application/json; charset=utf-8',
    'content-length': Buffer.byteLength(body),
  });
  res.end(body);
}

function readJson(req) {
  return new Promise((resolve, reject) => {
    let raw = '';
    req.on('data', (chunk) => {
      raw += chunk;
      if (raw.length > 1024 * 512) {
        reject(new Error('Payload too large'));
        req.destroy();
      }
    });
    req.on('end', () => {
      try {
        resolve(raw ? JSON.parse(raw) : {});
      } catch (error) {
        reject(error);
      }
    });
    req.on('error', reject);
  });
}

function broadcast(payload) {
  const message = JSON.stringify(payload);
  let delivered = 0;

  for (const client of clients) {
    if (client.readyState === WebSocket.OPEN) {
      client.send(message);
      delivered += 1;
    }
  }

  return delivered;
}

const server = http.createServer(async (req, res) => {
  if (req.method === 'GET' && req.url === '/health') {
    json(res, 200, {
      ok: true,
      clients: clients.size,
      wsPath,
      publishPath,
    });
    return;
  }

  if (req.method === 'POST' && req.url === publishPath) {
    if (secret && req.headers['x-ws-secret'] !== secret) {
      json(res, 401, { ok: false, error: 'Unauthorized' });
      return;
    }

    try {
      const payload = await readJson(req);
      const delivered = broadcast(payload);
      json(res, 200, { ok: true, delivered });
    } catch (error) {
      json(res, 400, { ok: false, error: error.message });
    }
    return;
  }

  json(res, 404, { ok: false, error: 'Not found' });
});

const wss = new WebSocketServer({ server, path: wsPath });

wss.on('connection', (socket) => {
  clients.add(socket);

  socket.send(JSON.stringify({
    event: 'connection.ready',
    resource: 'websocket',
    action: 'ready',
    message: 'Conexion de notificaciones establecida.',
    occurred_at: new Date().toISOString(),
  }));

  socket.on('close', () => clients.delete(socket));
  socket.on('error', () => clients.delete(socket));
});

server.listen(port, host, () => {
  console.log(`Map WebSocket server listening on ${host}:${port}${wsPath}`);
  console.log(`Publish endpoint ready on http://${host}:${port}${publishPath}`);
});
