#!/bin/bash

#put the status for writing job into a variable
jobstatus=$(cat /etc/osid/www/public_html/system/status.info)

#check if the status is equal to one
if [ $jobstatus -eq 1 ]
then
   #make sure the progress info file is empty
   cat /dev/null > /etc/osid/www/public_html/system/progress.info

   #set the status of the job to two (in progress)
   echo "2" > /etc/osid/www/public_html/system/status.info
   
   #put the contents of imagefile, devicelist, and unmount list into variables
   imagefile=$(cat /etc/osid/www/public_html/system/imagefile.info)
   devicelist=$(cat /etc/osid/www/public_html/system/devicelist.info)
   umountlist=$(cat /etc/osid/www/public_html/system/umountlist.info)

   #make sure all devices are unmounted
   eval "$umountlist"
 
   #run the dcfldd command passing the imagefile and device list
   eval "/usr/bin/dcfldd bs=4M if=/etc/osid/www/public_html/imgroot/$imagefile $devicelist sizeprobe=if statusinterval=1 2>&1 | tee /etc/osid/www/public_html/system/progress.info"
   
   #wait 5 seconds after dcfldd command has finished to ensure php script has a chance to read progress.info
   sleep 5

   #empty the contents of the info files ready for the next job
   cat /dev/null > /etc/osid/www/public_html/system/progress.info
   cat /dev/null > /etc/osid/www/public_html/system/imagefile.info
   cat /dev/null > /etc/osid/www/public_html/system/devicelist.info   
   cat /dev/null > /etc/osid/www/public_html/system/umountlist.info
   cat /dev/null > /etc/osid/www/public_html/system/debug.info

   #set the status of the job to zero
   echo "0" > /etc/osid/www/public_html/system/status.info

fi

exit
