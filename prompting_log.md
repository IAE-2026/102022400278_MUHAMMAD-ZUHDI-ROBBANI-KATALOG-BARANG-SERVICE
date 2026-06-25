- Programnya sudah jadi, saya harus menambahkan sso yang sudah ada soap yang udah ada dan pilih untuk rabbit mq begitu juga jwt

- Saya sedang membuat layanan Katalog Bagaimana caranya mengecek apakah token login (JWT) yang dipakai user itu asli dari sistem SSO

- Setelah tau loginnya valid saya ingin batas biar fitur tambah barang ini hanya bisa dibuka sama pengguna yang memiliki status Admin 
kasih gambaran logika untuk menolak akses kalo yang mencoba masuk adalah pengguna

- Sebelum barang boleh disimpan ke database saya sistem harus lapor dulu ke layanan Audit soap yang pake format xml gimana cara 
menyusun data barang yang baru diinput menjadi format XML yang bisa diterima oleh sistem

-Sistem audit sudah membalas laporan saya tapi bentuk balasannya teks XML yang panjang gimana cara paling mudah buat ambil status 
Sukses dan recip dari teks balasan di Laravel

-Saya mau mastiin alur saya sudah benar Urutannya saat ini adalah verifikasi JWT cek role admin kirim audit ke SOAP simpan data barang ke database 
lalu kirim ke RabbitMQ.

-Kalo SOAP Audit gagal respons proses simpan barang sebaiknya tetap dilanjutkan atau langsung dibatalkan
