# Mining configuration and automation for Ubuntu with GTX 1060 and 1070

## Automation through cron - Set the crontab like this as sudo: 

	`@reboot screen -S miner -dm bash -c 'echo waiting; sleep 60; cd /home/___USER__/__FOLDER/; ./occ.sh; echo waiting; sleep 10; ./execute.sh;'

	`0 9 * * * /sbin/shutdown -r now`

### Donations are welcome: ``0x9335fE2BCdca68407ed5Ae5FB196d2c69CAf96Da``

