#!/bin/bash
# WordPress Plugin pre-commit hook

set -e

message="Checking staged changes..."
git_status_egrep='^[MARC].+'

for i; do
	case "$i"
	in
		-m)
			message="Checking any uncommitted changes..."
			git_status_egrep='^.?[MARC].+'
			shift;;
	esac
done

echo $message

# Make sure the readme.md never gets out of sync with the readme.txt
generate_markdown_readme=$(find . -name generate-markdown-readme -print -quit)
if [ -n "$generate_markdown_readme" ]; then
	markdown_readme_path=$($generate_markdown_readme)
	git add $markdown_readme_path
fi
