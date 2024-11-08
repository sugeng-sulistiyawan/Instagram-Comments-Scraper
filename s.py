from instascrape import Post
import time

# URL dari postingan Instagram
url = "https://www.instagram.com/p/DCEkLYsS_Af/"

# Buat objek Post
post = Post(url)

try:
    # Muat data dari URL
    post.scrape()

    # Ambil semua komentar
    comments = post.comments

    # Cetak semua komentar
    for comment in comments:
        print(comment['text'])

    # Jika Anda ingin mengambil lebih banyak komentar, Anda bisa menggunakan pagination
    while post.has_next_page:
        time.sleep(2)  # Tambahkan jeda untuk menghindari rate limiting
        post.scrape_comments()
        comments = post.comments
        for comment in comments:
            print(comment['text'])
except ValueError as e:
    print(f"Error: {e}")
