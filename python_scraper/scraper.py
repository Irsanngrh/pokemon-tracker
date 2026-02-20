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
        
        while True:
            list_page = context.new_page()
            separator = '&' if '?' in base_url else '?'
            list_page.goto(f"{base_url}{separator}pageNo={page_no}")
            list_page.wait_for_timeout(4000)
            
            soup = BeautifulSoup(list_page.content(), 'html.parser')
            page_links = soup.find_all('a', href=re.compile(r'/id/card-search/detail/\d+/'))
            
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
            if links_added == 0:
                break
            page_no += 1

        cards_data = []
        
        for detail_url in detail_links:
            try:
                detail_page = context.new_page()
                detail_page.goto(detail_url)
                detail_page.wait_for_timeout(1500)
                dsoup = BeautifulSoup(detail_page.content(), 'html.parser')
                
                h1_text = dsoup.find('h1').get_text(separator=' ', strip=True) if dsoup.find('h1') else "Unknown"
                
                category = "Basic"
                card_name = h1_text
                
                prefixes = ["Stage 1", "Stage 2", "VMAX", "VSTAR", "MEGA", "Basic", "Item", "Supporter", "Stadium"]
                for pref in prefixes:
                    if h1_text.lower().startswith(pref.lower() + " "):
                        category = pref
                        card_name = h1_text[len(pref):].strip()
                        break
                
                if category == "Basic":
                    stage_img = dsoup.find('img', alt=re.compile(r'Stage|Basic|VMAX|VSTAR|Mega|ex|Item|Supporter|Stadium', re.IGNORECASE))
                    if stage_img:
                        alt_text = stage_img.get('alt', '')
                        if alt_text.lower() != 'basic':
                            category = alt_text
                
                card_number = "---"
                number_elem = dsoup.find(string=re.compile(r'\d+/\d+'))
                if number_elem:
                    card_number = number_elem.strip()
                
                illustrator = "Unknown"
                illus_heading = dsoup.find(string=re.compile(r'Ilustrator|Illustrator'))
                if illus_heading and illus_heading.find_parent():
                    ns = illus_heading.find_parent().find_next_sibling()
                    if ns:
                        illustrator = ns.get_text(strip=True)
                
                image_url = ""
                img_tag = dsoup.find('img', src=re.compile(r'/card-img/'))
                if img_tag:
                    src = img_tag.get('src')
                    image_url = 'https://asia.pokemon-card.com' + src if not src.startswith('http') else src

                details = {
                    'attacks': [],
                    'weakness': '--',
                    'resistance': '--',
                    'retreat': '--',
                    'regulation': '-'
                }

                attack_areas = dsoup.find_all('h4')
                for area in attack_areas:
                    atk_name = area.get_text(strip=True)
                    atk_dmg = area.find_next_sibling('span').get_text(strip=True) if area.find_next_sibling('span') else ""
                    atk_eff = area.find_next_sibling('p').get_text(strip=True) if area.find_next_sibling('p') else ""
                    if atk_name and atk_name not in ["Kelemahan", "Resistansi", "Mundur"]:
                        details['attacks'].append({'name': atk_name, 'damage': atk_dmg, 'effect': atk_eff})

                tables = dsoup.find_all('table')
                for table in tables:
                    text_content = table.get_text()
                    if "Kelemahan" in text_content:
                        tds = table.find_all('td')
                        if len(tds) >= 3:
                            weak_img = tds[0].find('img')
                            weak_type = weak_img.get('alt') if weak_img else "Unknown"
                            weak_val = tds[0].get_text(strip=True)
                            details['weakness'] = f"{weak_type} {weak_val}".strip() if weak_img else weak_val
                            
                            res_img = tds[1].find('img')
                            res_type = res_img.get('alt') if res_img else ""
                            res_val = tds[1].get_text(strip=True)
                            details['resistance'] = f"{res_type} {res_val}".strip() if res_val else "--"
                            
                            ret_imgs = tds[2].find_all('img')
                            details['retreat'] = str(len(ret_imgs)) if ret_imgs else "--"

                reg_elem = dsoup.find(string=re.compile(r'^[A-Z]$'))
                if reg_elem and len(reg_elem.strip()) == 1:
                    details['regulation'] = reg_elem.strip()

                cards_data.append({
                    'name': card_name,
                    'card_number': card_number,
                    'image_url': image_url,
                    'category': category,
                    'illustrator': illustrator,
                    'details': details
                })
                detail_page.close()
            except Exception:
                pass
                
        browser.close()
        print(json.dumps(cards_data))

if __name__ == "__main__":
    if len(sys.argv) > 1:
        scrape_cards(sys.argv[1])