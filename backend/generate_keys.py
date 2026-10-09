"""Generator kunci keamanan.

Jalankan: `python generate_keys.py`
Tempel hasilnya ke `.env`. Jangan commit nilai asli.
"""

import secrets

from cryptography.fernet import Fernet


def main() -> None:
    print("# Salin baris berikut ke .env")
    print(f"ENCRYPTION_KEY={Fernet.generate_key().decode()}")
    print(f"HMAC_KEY={secrets.token_urlsafe(48)}")
    print(f"JWT_SECRET={secrets.token_urlsafe(48)}")
    print("# PIN override (pilih angka rahasia yang kuat)")
    print(f"OVERRIDE_PIN={secrets.randbelow(10**6):06d}")


if __name__ == "__main__":
    main()
