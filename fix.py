import os

def fix_mojibake(filepath):
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            text = f.read()
            
        text = text.replace('I help you achieve tangible results Ã¢â‚¬â€ from improving', 'I help you achieve tangible results — from improving')
        text = text.replace('ðŸ“„ Download CV', '📄 Download CV')
        text = text.replace('<div class="icon">Ã°Å¸Å¡â‚¬</div>', '<div class="icon">🚀</div>')
        text = text.replace('<div class="icon">ðŸš€</div>', '<div class="icon">🚀</div>')
        text = text.replace('<div class="icon">Ã°Å¸â€</div>', '<div class="icon">🔍</div>')
        text = text.replace('<div class="icon">ðŸ” </div>', '<div class="icon">🔍</div>')
        text = text.replace('<div class="icon">Ã°Å¸ÂÂ¢</div>', '<div class="icon">🏢</div>')
        text = text.replace('<div class="icon">ðŸ ¢</div>', '<div class="icon">🏢</div>')
        text = text.replace('â€” Director, Mahalak', '— Director, Mahalak')
        text = text.replace('â€” Founder, Hader', '— Founder, Hader')
        text = text.replace('â€” CEO, 33northlb', '— CEO, 33northlb')
        text = text.replace('ðŸ“§ firasdaher16@gmail.com', '📧 firasdaher16@gmail.com')
        text = text.replace('ðŸ“ž +961 81 340 801', '📞 +961 81 340 801')
        
        # additional exact matches just in case
        text = text.replace('Ã¢â‚¬â€', '—')
        text = text.replace('ðŸ“„', '📄')
        text = text.replace('Ã°Å¸Å¡â‚¬', '🚀')
        text = text.replace('Ã°Å¸ÂÂ¢', '🏢')
        text = text.replace('â€”', '—')
        text = text.replace('ðŸ“§', '📧')
        text = text.replace('ðŸ“ž', '📞')
        text = text.replace('ðŸ”', '🔍')
        text = text.replace('Ã°Å¸â€', '🔍')
        
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(text)
    except Exception as e:
        print(f"Failed {filepath}: {e}")

for root, dirs, files in os.walk('.'):
    for file in files:
        if file.endswith('.html'):
            fix_mojibake(os.path.join(root, file))
