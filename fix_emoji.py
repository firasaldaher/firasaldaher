import os

svg_dl = '<svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 -960 960 960" width="20" fill="currentColor" style="vertical-align: middle; margin-right: 4px;"><path d="M480-320 280-520l56-58 104 104v-326h80v326l104-104 56 58-200 200ZM240-160q-33 0-56.5-23.5T160-240v-120h80v120h480v-120h80v120q0 33-23.5 56.5T720-160H240Z"/></svg>'
svg_email = '<svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 -960 960 960" width="20" fill="currentColor" style="vertical-align: middle; margin-right: 8px;"><path d="M160-160q-33 0-56.5-23.5T80-240v-480q0-33 23.5-56.5T160-800h640q33 0 56.5 23.5T880-720v480q0 33-23.5 56.5T800-160H160Zm320-280L160-640v400h640v-400L480-440Zm0-80 320-200H160l320 200ZM160-640v-80 480-400Z"/></svg>'
svg_phone = '<svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 -960 960 960" width="20" fill="currentColor" style="vertical-align: middle; margin-right: 8px;"><path d="M798-120q-125 0-247-54.5T329-329Q229-429 174.5-551T120-798q0-18 12-30t30-12h162q14 0 25 9.5t13 22.5l26 140q2 16-1 27t-11 19l-97 98q20 37 47.5 71.5T387-386q31 31 65 57.5t72 48.5l94-94q9-9 23.5-13.5T670-390l138 28q14 4 23 14.5t9 23.5v162q0 18-12 30t-30 12ZM241-600l66-66-17-94h-89q5 41 14 81t26 79Zm358 358q39 17 79.5 27t81.5 13v-88l-94-19-67 67ZM241-600Zm358 358Z"/></svg>'
svg_code = '<svg xmlns="http://www.w3.org/2000/svg" height="40" viewBox="0 -960 960 960" width="40" fill="var(--primary)"><path d="M320-240 80-480l240-240 57 57-184 184 183 183-56 56Zm320 0-57-57 184-184-183-183 56-56 240 240-240 240Z"/></svg>'
svg_search = '<svg xmlns="http://www.w3.org/2000/svg" height="40" viewBox="0 -960 960 960" width="40" fill="var(--primary)"><path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/></svg>'
svg_build = '<svg xmlns="http://www.w3.org/2000/svg" height="40" viewBox="0 -960 960 960" width="40" fill="var(--primary)"><path d="M80-120v-720h400v160h400v560H80Zm80-80h240v-80H160v80Zm0-160h240v-80H160v80Zm0-160h240v-80H160v80Zm0-160h240v-80H160v80Zm320 480h240v-80H480v80Zm0-160h240v-80H480v80Zm0-160h240v-80H480v80Z"/></svg>'
svg_dark = '<svg id="theme-icon" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor"><path d="M480-120q-150 0-255-105T120-480q0-150 105-255t255-105q14 0 27.5 1t26.5 3q-41 29-65.5 75.5T444-660q0 90 63 153t153 63q55 0 101-24.5t75-65.5q2 13 3 26.5t1 27.5q0 150-105 255T480-120Z"/></svg>'

def replace_in_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        text = f.read()
    
    text = text.replace('📄', svg_dl)
    text = text.replace('📧', svg_email)
    text = text.replace('📞', svg_phone)
    text = text.replace('<div class="icon">🚀</div>', f'<div class="icon">{svg_code}</div>')
    text = text.replace('<div class="icon">🔍</div>', f'<div class="icon">{svg_search}</div>')
    text = text.replace('<div class="icon">🏢</div>', f'<div class="icon">{svg_build}</div>')
    text = text.replace('title="Toggle Dark Mode">🌙</button>', f'title="Toggle Dark Mode">{svg_dark}</button>')

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(text)

for root, dirs, files in os.walk('.'):
    for file in files:
        if file.endswith('.html'):
            replace_in_file(os.path.join(root, file))

# Fix main.js for toggle logic
with open('assets/js/main.js', 'r', encoding='utf-8') as f:
    js = f.read()

svg_light = '<svg id="theme-icon" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor"><path d="M480-360q50 0 85-35t35-85q0-50-35-85t-85-35q-50 0-85 35t-35 85q0 50 35 85t85 35Zm0 80q-83 0-141.5-58.5T280-480q0-83 58.5-141.5T480-700q83 0 141.5 58.5T680-480q0 83-58.5 141.5T480-280ZM200-440H40v-80h160v80Zm720 0H760v-80h160v80ZM440-760v-160h80v160h-80Zm0 720v-160h80v160h-80ZM256-650l-101-97 57-59 96 100-52 56Zm492 496-97-101 53-55 101 97-57 59Zm-98-550 97-101 59 57-100 96-56-52ZM154-212l101-97 55 53-97 101-59-57Zm326-268Z"/></svg>'

js = js.replace("toggleButton.textContent = '☀️';", f"toggleButton.innerHTML = '{svg_light}';")
js = js.replace("toggleButton.textContent = '🌙';", f"toggleButton.innerHTML = '{svg_dark}';")

with open('assets/js/main.js', 'w', encoding='utf-8') as f:
    f.write(js)
