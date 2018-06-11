#!/bin/bash
cd ~/
git pull public_html master
cp -r html/application/modules/* public_html/application/modules/
