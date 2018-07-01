@echo off

echo "Making docs..."
"bin\phpdoc.bat -d ./private/class/ -t ./public/sitedocs"
