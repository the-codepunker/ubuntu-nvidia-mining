# Mining configuration and automation for Ubuntu 16.04 with Nvidia GPUs ( GTX 1060 and 1070 )

## Step by step guide
1. Assemble the rig with all graphics cards and make sure they're all "spinning"
2. Install Ubuntu desktop from a bootable USB stick by following the on-screen step-by-step guide. If the Ubuntu installer doesn't start, hit F6 during boot and select [nomodeset](https://drive.google.com/file/d/1mF225NO0mqGZ_adTn0EyxVqzus31bNJJ/view?usp=drivesdk) from the options menu in the bottom right area.
3. Make sure you check the "Install third party software for graphics..." - this will preinstall the nvidia drivers on your system. Once the OS is installed, fire up a terminal and install git. `sudo apt-get install git && sudo apt-get install openssh-server && sudo apt-get install screen && sudo apt-get install htop`
4. Go into your router management console and forward a port to port 22 on the mining machine IP address. For example, I forward port 2017 on my router to port 22 on local address 192.168.0.69 which is the reserved IP address of the miner.
5. Edit the `/etc/ssh/sshd_config` and where it says `PasswordAuthentication no` change it to `PasswordAuthentication yes`. This is not the most secure option, but if you know this you will also know how to set up key based authentication over ssh.
6. Press `ctrl + alt + f1` to switch to a text-only terminal.
7. Run `lspci -v | grep VGA` to make sure the system sees all 12 GPUs... if it doesn't something is wrong with the build.
8. Update the repository database and install any updates: `sudo apt update && sudo apt upgrade`... then restart: `shutdown -r now`
9. If you haven't already installed the nvidia drivers (checked the checkbox from step 3), then you will need to install them manually: Add the Nvidia drivers repository: `sudo add-apt-repository ppa:graphics-drivers/ppa` and continue with the next instructions, **otherwise jump to step 11**.
10. Go in text mode `ctrl + alt + f1` and install the current Nvidia drivers: `sudo apt-get install nvidia-375` (or nvidia 384). If the install fails or anything else goes wrong you can always run `sudo apt-get purge nvidia-*` to return to the previous step
11. Go into the "Software and Updates" application -> "Additional Drivers" tab and enable the nvidia proprietary driver. Then restart!
11. Select the Nvidia GPUs `sudo prime-select nvidia` and enable them all for over-clocking `nvidia-xconfig --enable-all-gpus`
12. Create a folder somewhere in you home directory and clone this repository... `cd YOUR_FOLDER && git clone https://github.com/the-codepunker/ubuntu-nvidia-mining.git .` 
13. Make a copy of the default Xorg configuration file `sudo mv /etc/X11/xorg.conf /etc/X11/xorg_bak.conf`
14. Enable full over-clocking capabilities for your GPUs `sudo nvidia-xconfig -a --cool-bits=31 --allow-empty-initial-configuration`
15. Execute the commands below to populate the xorg.conf file with the required parameters for enabling over-clocking:
	- `sudo sed -i '/Option         "AllowEmptyInitialConfiguration" "True"/a    Option         "ConnectedMonitor" "DFP-0"' /etc/X11/xorg.conf`
	- `sudo sed -i '/Option         "ConnectedMonitor" "DFP-0"/a    Option         "CustomEDID" "DFP-0:/etc/X11/dfp0.edid"' /etc/X11/xorg.conf`
16. Copy the fake display `dfp0.edid` from my git repo to the X11 folder: `sudo cp dfp0.edid /etc/X11/dfp0.edid`
17. Make sure lightdm doesn't overwrite your xorg.conf file on the next reboot: `sudo chattr +i /etc/X11/xorg.conf` and restart. If everything went well... your xorg file will remain unchanged, but the system will no longer boot in graphics mode so you will need to switch to text-only mode.
18. To fire up the miner you will need to first execute my script for over-clocking located in the root folder of the repo: `sudo ./occ.sh`. If it works it will tell you that it changed the memory frequency of each GPU and the maximum power it can draw.
19. Finally edit the file called `execute.sh` and add your address instead of mine. Then run `sudo ./execute.sh` and **BOOM**.
20. To monitor your miner you can run `nvidia-smi` to see power draw and GPU temperatures. If you run `nvidia-smi -q -d CLOCK` you will see core and memory clocks for each GPU.

## Automation through cron - Set the crontab like this as sudo: 

	@reboot screen -S miner -dm bash -c 'echo waiting; sleep 60; cd /home/___USER__/__FOLDER/; ./occ.sh; echo waiting; sleep 10; ./execute.sh;'

	`0 9 * * * /sbin/shutdown -r now

### If you want tips on how to set up the hardware check out [my post](https://www.codepunker.com/blog/ethereum-mining-on-ubuntu-16-04-with-nvidia-gpus).

### Donations are welcome: ``0x9335fE2BCdca68407ed5Ae5FB196d2c69CAf96Da``

