import instaloader
import json
import re
from datetime import datetime

# Buat objek Instaloader
L = instaloader.Instaloader()

# Muat sesi dari file
L.load_session_from_file('sugengsulistiyawan')

# Daftar shortcodes dengan label
shortcodes = {
    'DCEjnM_yHDk': 'ILM SMA',
    'DCEkLYsS_Af': 'ILM SMP',
    'DCEiS3MSsWn': 'POSTER SMA',
    'DCEis1CSjm6': 'POSTER SMP',
}

def is_bot(username):
    # Simple heuristic to check if a username is likely a bot
    bot_patterns = [
        r'\d{4,}',  # Contains 4 or more consecutive digits
        r'(.)\1{2,}',  # Contains 3 or more consecutive identical characters
        r'bot',  # Contains the word 'bot'
        r'test',  # Contains the word 'test'
        r'private',  # Contains the word 'private'
    ]
    for pattern in bot_patterns:
        if re.search(pattern, username.lower()):
            return True
    return False

for shortcode, label in shortcodes.items():
    # Ambil postingan menggunakan shortcode
    post = instaloader.Post.from_shortcode(L.context, shortcode)

    # Ambil semua komentar
    comments = post.get_comments()

    # Simpan komentar ke dalam list
    comments_list = []
    for comment in comments:
        # Konversi timestamp ke zona waktu UTC+7
        timestamp_utc = comment.created_at_utc
        print(f"{comment.owner.username}: {comment.text} - {timestamp_utc}")
        comments_list.append({
            'username': f'{comment.owner.username}',
            'text': f'{comment.text}',
            'timestamp': f'{timestamp_utc}',
            'is_bot': is_bot(comment.owner.username),
        })

    # Simpan hasil ke file JSON
    output_file = f'{label}.json'
    with open(output_file, 'w') as f:
        json.dump(comments_list, f, indent=4)

    print(f"Comments have been saved to {output_file}")
