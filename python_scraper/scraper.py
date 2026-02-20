import sys
import json
import re
from playwright.sync_api import sync_playwright
from bs4 import BeautifulSoup

def scrape_cards(base_url):
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        context = browser.new_context()
        
        detail_links = []
        page_no = 1
        
        # FASE 1: Menjelajah seluruh halaman (Pagination) sampai habis
        while True:
            list_page = context.new_page()
            
            # Menyisipkan parameter pageNo (halaman 1, 2, 3...) ke URL
            separator = '&' if '?' in base_url else '?'
            paginated_url = f"{base_url}{separator}pageNo={page_no}"
            
            list_page.goto(paginated_url)
            list_page.wait_for_timeout(2500) # Tunggu loading gambar selesai
            
            soup = BeautifulSoup(list_page.content(), 'html.parser')
            page_links = soup.find_all('a', href=re.compile(r'/id/card-search/detail/\d+/'))
            
            # Jika halaman ini kosong (tidak ada link kartu), berarti kita sudah di halaman terakhir
            if not page_links:
                list_page.close()
                break
                
            links_added = 0
            for link in page_links:
                href = link.get('href')
                if href:
                    full_url = 'https://asia.pokemon-card.com' + href if not href.startswith('http') else href
                    if full_url not in detail_links:
                        detail_links.append(full_url)
                        links_added += 1
            
            list_page.close()
            
            # Mencegah error infinite loop jika website mengulang kartu yang sama
            if links_added == 0:
                break
                
            page_no += 1

        cards_data = []
        
        # FASE 2: Masuk ke masing-masing halaman detail kartu
        for detail_url in detail_links:
            try:
                detail_page = context.new_page()
                detail_page.goto(detail_url)
                detail_page.wait_for_timeout(1000)
                
                dsoup = BeautifulSoup(detail_page.content(), 'html.parser')
                texts = [t.strip() for t in dsoup.stripped_strings if t.strip()]
                
                # Nama Kartu
                card_name = dsoup.find('h1').get_text(strip=True) if dsoup.find('h1') else (texts[1] if len(texts)>1 else "Unknown")
                
                # Kategori
                category = "Standard"
                for t in texts[:15]:
                    if t in ["Stage 1", "Stage 2", "Basic", "VMAX", "VSTAR", "Item", "Supporter", "Stadium"]:
                        category = t
                        break

                # Urutan Kartu
                card_number = "---"
                for t in texts:
                    if re.match(r'^\d+/\d+$', t) or re.match(r'^[A-Z0-9\-]+\s+\d+/\d+$', t):
                        card_number = t.split()[-1]
                        break
                        
                # Ilustrator
                illustrator = "Unknown"
                if "Ilustrator" in texts:
                    idx = texts.index("Ilustrator")
                    if idx + 1 < len(texts):
                        illustrator = texts[idx + 1]

                # Keterangan Serangan
                description = ""
                if "Serangan" in texts:
                    start = texts.index("Serangan") + 1
                    end = texts.index("Evolusi") if "Evolusi" in texts else (texts.index("Ilustrator") if "Ilustrator" in texts else start + 5)
                    description = "\n".join(texts[start:end])
                elif "Peraturan" in texts:
                    start = texts.index("Peraturan") + 1
                    end = texts.index("Ilustrator") if "Ilustrator" in texts else start + 3
                    description = "\n".join(texts[start:end])

                # Gambar
                image_url = ""
                img_tag = dsoup.find('img', src=re.compile(r'/card-img/'))
                if img_tag:
                    src = img_tag.get('src')
                    image_url = 'https://asia.pokemon-card.com' + src if not src.startswith('http') else src
                
                cards_data.append({
                    'name': card_name,
                    'card_number': card_number,
                    'image_url': image_url,
                    'category': category,
                    'illustrator': illustrator,
                    'description': description
                })
                detail_page.close()
            except Exception:
                pass
                
        browser.close()
        print(json.dumps(cards_data))

if __name__ == "__main__":
    if len(sys.argv) > 1:
        target_url = sys.argv[1]
        scrape_cards(target_url)