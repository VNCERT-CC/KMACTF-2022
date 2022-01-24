# ./CTFd/CTFd/themes/core/static/js/count-down.js
# ./CTFd/CTFd/themes/core/static/img/logo.png
# ./CTFd/CTFd/themes/core/templates/challenges.html
# ./CTFd/CTFd/themes/core/templates/errors/403.html
unzip -o theme_core_patch.zip -d .
exit 0
curl https://gist.githubusercontent.com/vinhjaxt/fa4208fd6902dd8b2f4d944fa6e7f2af/raw/logo-hvktmm.png -o ./CTFd/CTFd/themes/core/static/img/logo.png
cp count-down.js ./CTFd/CTFd/themes/core/static/js/count-down.js
