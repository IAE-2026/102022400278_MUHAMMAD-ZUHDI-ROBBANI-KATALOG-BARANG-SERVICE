Analisis Tugas 3 - Katalog Barang Service

1. Transaksi Kritis

Transaksi yang dipilih sebagai transaksi kritis adalah proses menambahkan barang baru ke katalog lelang melalui endpoint POST /admin/items.
Transaksi ini dianggap paling penting karena menjadi titik awal seluruh proses lelang. Barang yang ditambahkan ke katalog akan digunakan oleh layanan lain, seperti layanan bidding dan invoice. Jika terjadi kesalahan pada tahap ini, maka data yang digunakan oleh sistem lain juga akan ikut salah.
Karena alasan tersebut, proses penambahan barang hanya dapat dilakukan oleh pengguna yang memiliki role Admin. Sebelum barang disimpan ke database, sistem juga melakukan pencatatan aktivitas ke layanan SOAP Audit agar terdapat bukti bahwa aktivitas tersebut telah dilakukan.

2. Integrasi dengan SSO

Agar proses bisnis aman, seluruh proses yang mengubah atau tambahin data harus melalui validasi menggunakan SSO.
Alur penggunaan SSO sebagai berikut:
Admin melakukan login ke sistem SSO.
SSO memberikan JWT Token kepada admin.
Admin melakukan post items ke Katalog Barang Service dengan menyertakan token tersebut.
Katalog Barang Service melakukan verifikasi JWT.
Setelah token dinyatakan valid, sistem melakukan pengecekan role lokal.
Jika pengguna memiliki role Admin maka proses dapat dilanjutkan.
Jika token tidak valid atau role tidak sesuai maka permintaan langsung ditolak.

dengan integrasi sso seperti di atas akan membuat keamanan pada probis katalog barang terjamin karn tidak semua user bisa mengakses

3. Integrasi SOAP Audit

Setelah proses autentikasi dan otorisasi berhasil dilakukan, sistem akan mengirimkan data audit ke layanan SOAP Audit.
Informasi yang dikirim adalah:
TeamID
ActivityName dengan nilai ItemCreated
LogContent yang berisi data barang yang sedang ditambahkan

Setelah itu SOAP Audit akan memberikan No receipt Pada Katalog barang, Sebagai tanda bahwa SOAP Audit berhasil.

4. Integrasi RabbitMQ

Setelah data barang berhasil disimpan ke database, sistem akan mengirimkan data ke RabbitMQ.
Data yang dikirim adalah:
item.created
Data tersebut berisi informasi mengenai barang yang baru ditambahkan ke katalog.

Dengan RabbitMQ, layanan lain tidak harus sering melakukan pengecekan database unutk mengetahui adanya barang baru. layanan lain langsung dapat mengatahui jika ada barang baru.

Contoh layanan yang dapat menerima event ini adalah:

Layanan Bidding
Layanan invoice

Pendekatan ini membuat komunikasi antar layanan menjadi lebih cepat dan Membuang waktu.

5. Sequence Diagram Internal

Sequence Diagram berikut menggambarkan proses transaksi kritis saat Admin menambahkan barang baru ke dalam katalog lelang.

Admin melakukan login ke SSO dan memperoleh JWT Token.
Admin mengirim permintaan POST /admin/items beserta JWT Token ke Katalog Barang Service.
Katalog Barang Service melakukan verifikasi JWT.
Jika JWT tidak valid maka sistem mengembalikan respons 401 Forbidden dan proses berhenti.
Jika JWT valid maka sistem melanjutkan proses audit ke SOAP Audit Service.
SOAP Audit Service menerima aktivitas ItemCreated dan mengembalikan status serta nomor receipt.
Setelah menerima respons dari SOAP Audit Service, data barang disimpan ke database.
Database mengembalikan status bahwa data berhasil disimpan.
Katalog Barang Service mengirim event item.created ke RabbitMQ.
RabbitMQ menerima event dan meneruskannya ke layanan yang membutuhkan.
Sistem mengembalikan respons 201 Created kepada Admin sebagai tanda bahwa barang berhasil ditambahkan.

