#!/bin/bash -e
# Absolute path to this script
SCRIPT=`readlink -f $0`
# Absolute directory this script is in
SCRIPTPATH=`dirname $SCRIPT`

# cd $SCRIPTPATH/../airtime_mvc/
cd /usr/share/airtime/
path=`pwd`
cd build
sed -i s#"project\.home =.*$"#"project.home = $path"#g build.properties
/usr/share/airtime/library/propel/generator/bin/propel-gen
