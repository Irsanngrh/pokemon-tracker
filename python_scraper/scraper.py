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
            list_page.wait_for_timeout(3000)
            
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

        series_match = re.search(r'expansionCodes=([^&]+)', base_url)
        series_code = series_match.group(1) if series_match else "--"
        cards_data = []
        
        for detail_url in detail_links:
            try:
                official_id_match = re.search(r'/detail/(\d+)/', detail_url)
                official_id = official_id_match.group(1) if official_id_match else None
                if not official_id:
                    continue

                detail_page = context.new_page()
                detail_page.goto(detail_url)
                detail_page.wait_for_timeout(1500)
                dsoup = BeautifulSoup(detail_page.content(), 'html.parser')
                
                h1_text = dsoup.find('h1').get_text(separator=' ', strip=True) if dsoup.find('h1') else "Unknown"
                category = "Basic"
                header_h3 = dsoup.select_one('h3.commonHeader')
                if header_h3:
                    h3_val = header_h3.get_text(strip=True)
                    if h3_val in ["Item", "Supporter", "Stadium", "Pokémon Tool", "Energi Spesial"]:
                        category = h3_val
                    else:
                        prefixes = ["Stage 1", "Stage 2", "VMAX", "VSTAR", "MEGA", "Basic"]
                        for pref in prefixes:
                            if h1_text.lower().startswith(pref.lower() + " "):
                                category = pref
                                break
                        if category == "Basic":
                            stage_img = dsoup.find('img', alt=re.compile(r'Stage|Basic|VMAX|VSTAR|Mega|ex', re.IGNORECASE))
                            if stage_img:
                                alt_text = stage_img.get('alt', '')
                                if alt_text.lower() != 'basic':
                                    category = alt_text
                
                card_name = h1_text
                for pref in ["Stage 1", "Stage 2", "VMAX", "VSTAR", "MEGA", "Basic", "Item", "Supporter", "Stadium", "Pokémon Tool", "Energi Spesial"]:
                    if h1_text.lower().startswith(pref.lower() + " "):
                        card_name = h1_text[len(pref):].strip()
                        break
                
                card_number = "--"
                number_elem = dsoup.find(string=re.compile(r'\d+/\d+'))
                if number_elem:
                    card_number = number_elem.strip()
                
                illustrator = "--"
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
                    'hp': '',
                    'type_url': '',
                    'series': series_code,
                    'attacks': [],
                    'trainer_effect': '',
                    'weakness_icon': '',
                    'weakness_val': '--',
                    'resistance_icon': '',
                    'resistance_val': '--',
                    'retreat_icons': []
                }

                if category in ["Item", "Supporter", "Stadium", "Pokémon Tool", "Energi Spesial"]:
                    effect_elem = dsoup.select_one('.skillEffect')
                    if effect_elem:
                        details['trainer_effect'] = effect_elem.get_text(strip=True)
                else:
                    main_info = dsoup.select_one('.mainInfomation, .mainInformation')
                    if main_info:
                        hp_num = main_info.select_one('.number')
                        if hp_num: details['hp'] = hp_num.get_text(strip=True)
                        type_img = main_info.select_one('img')
                        if type_img:
                            src = type_img.get('src', '')
                            details['type_url'] = 'https://asia.pokemon-card.com' + src if src and not src.startswith('http') else src

                    skills = dsoup.select('.skill')
                    for skill in skills:
                        atk_name = ""
                        atk_dmg = ""
                        atk_eff = ""
                        cost_icons = []
                        n_elem = skill.select_one('.skillName, .abilityName')
                        if n_elem: atk_name = n_elem.get_text(strip=True)
                        d_elem = skill.select_one('.skillDamage')
                        if d_elem: atk_dmg = d_elem.get_text(strip=True)
                        e_elem = skill.select_one('.skillEffect, .abilityEffect')
                        if e_elem: atk_eff = e_elem.get_text(strip=True)
                        c_elem = skill.select_one('.skillCost')
                        if c_elem:
                            for img in c_elem.find_all('img'):
                                src = img.get('src', '')
                                if src: cost_icons.append('https://asia.pokemon-card.com' + src if not src.startswith('http') else src)
                        if atk_name:
                            details['attacks'].append({'name': atk_name, 'damage': atk_dmg, 'effect': atk_eff, 'cost': cost_icons})

                    tables = dsoup.find_all('table')
                    for table in tables:
                        text_content = table.get_text()
                        if "Kelemahan" in text_content:
                            tds = table.find_all('td')
                            if len(tds) >= 3:
                                weak_img = tds[0].find('img')
                                if weak_img:
                                    src = weak_img.get('src', '')
                                    details['weakness_icon'] = 'https://asia.pokemon-card.com' + src if src and not src.startswith('http') else src
                                details['weakness_val'] = tds[0].get_text(strip=True)
                                res_img = tds[1].find('img')
                                if res_img:
                                    src = res_img.get('src', '')
                                    details['resistance_icon'] = 'https://asia.pokemon-card.com' + src if src and not src.startswith('http') else src
                                details['resistance_val'] = tds[1].get_text(strip=True)
                                ret_imgs = tds[2].find_all('img')
                                for r_img in ret_imgs:
                                    src = r_img.get('src', '')
                                    details['retreat_icons'].append('https://asia.pokemon-card.com' + src if src and not src.startswith('http') else src)

                cards_data.append({
                    'official_id': official_id,
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