# polylang-intro-page
Polylang Extension for an intro / language selection page

This plugin allows to have an intro / language selection page.

The intro page is then located at the root URI (www.example.com) and the actual front page is located at the individual lanuage paths (www.example.com/en; www.example.com/fr; www.example.com/de).
Current plugin implementation uses the page with slug `intro` as the intro page.

This plugin also provides a `POLYLANG_SWITCHER_INTRO` shortcode which is meant to be used to output in the intro page the language switcher to front page.

'intro' class is added to the body element of intro page.
