#!/bin/bash
cd /home/dhfwjaco
git pull public_html master
cp -r /home/dhfwjaco/html/application/modules/* /home/dhfwjaco/public_html/application/modules/
