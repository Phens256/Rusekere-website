import threading
import unittest
from http.server import BaseHTTPRequestHandler, HTTPServer

from sitewatch.checker import check_url


class Handler(BaseHTTPRequestHandler):
    def do_GET(self):
        code = 404 if self.path == "/missing" else 200
        self.send_response(code)
        self.send_header("Content-Type", "text/html")
        self.end_headers()
        self.wfile.write(b"<html><body>hello</body></html>")

    def log_message(self, *args):
        pass


class CheckUrlTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.server = HTTPServer(("127.0.0.1", 0), Handler)
        cls.base = f"http://127.0.0.1:{cls.server.server_port}"
        threading.Thread(target=cls.server.serve_forever, daemon=True).start()

    @classmethod
    def tearDownClass(cls):
        cls.server.shutdown()

    def test_ok(self):
        r = check_url(self.base + "/")
        self.assertTrue(r.ok)
        self.assertEqual(r.status, 200)

    def test_404_is_not_ok(self):
        r = check_url(self.base + "/missing")
        self.assertFalse(r.ok)
        self.assertEqual(r.status, 404)

    def test_unreachable_reports_error(self):
        r = check_url("http://127.0.0.1:9", timeout=1)
        self.assertFalse(r.ok)
        self.assertIsNotNone(r.error)


if __name__ == "__main__":
    unittest.main()
