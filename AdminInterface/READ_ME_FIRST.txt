Copy both files into:
C:\xampp\htdocs\LoungeRoyale\AdminInterface

Files:
- home.php
- admin_overrides.css

Choose Replace for both files.

This fixes two things:
1. home.php now loads admin_overrides.css with a version tag so Chrome does not reuse the old cached CSS.
2. admin_overrides.css forces the hero hand to use the same customer hand size/position.

After copying, press Ctrl + F5 on the admin home page.
