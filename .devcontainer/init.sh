# The following line is needed to ensure that the "xdg-open" command is available in the container, which is required for opening links in the default browser.
[ -f "$BROWSER" ] && ! command -v xdg-open > /dev/null && sudo ln -s "$BROWSER" /usr/local/bin/xdg-open
cat >> ~/.bashrc << 'EOF'
if [ -z "$SSH_AUTH_SOCK" ] || ! ssh-add -l >/dev/null 2>&1; then
  eval "$(ssh-agent -s)"
fi
EOF