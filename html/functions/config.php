<?php
/*
KVM-VDI
Tadas Ustinavičius

Vilnius University.
Center of Information Technology Development.


Vilnius,Lithuania.
2017-03-10
*/


###################Dashboard config##########################
$serviceurl='http://192.168.100.130';
$language='en_EN';
#define salt string, which will be used to generate passwords.
$salt='SecretString';
#write debug log to web server error.log file.
$write_debug_log=false;
#Websockify server address (if using HTML5 client)
$websockets_address='192.168.100.130';
#Port of websockify server (if using HTML5 client)
$websockets_port='5959';
#If $send_email_alerts is enabled, dashboard will send alerts to
#$alert_email_to critical error will occur. Requires PEAR Mail library.
$send_email_alerts=false;
$alert_email_to='kvm-admin@domain.tld';
$alert_email_from='kvm-dashboard@domain.tld';
$smtp_server_address='smtp.server.tld';
$smtp_server_port='25';
$smtp_ssl=false;
$smtp_auth=false;
$smtp_auth_username='someuser';
$smtp_auth_password='somepass';
# If true, dashboard will substitute SPICE address
# with hypervisor address.
$use_hypervisor_address = true;
############################################################


##################VM config#################################
#Time in minutes, after which VM will return to pool if
#thin client disconnects:
$return_to_pool_after=5;
#Enable this if you want to reset VMs each time they are provided
#to client. This will not affect machines, which are taken over by
#another thin client with the same username, within time, specified
#in $return_to_pool_after.
$reset_vm=0;
############################################################


####################Active Directory/LDAP logins#############
#please specify your LDAP backend:
$LDAP_backend='activedir';
#$LDAP_backend='ldap';
#Domain part to be added to username (only for Active Directory).
$domain_name='domain.tld';
#Address of ACtive Directory/LDAP server.
$LDAP_host='192.168.100.130';
#Username and password to bind to LDAP server (not deeded by Active Directory):
$LDAP_username='uid=USERNAME,ou=bind users,dc=domain,dc=tld';
$LDAP_password='ldappassword';
#Base DN pattern:
$base_dn = 'ou=People,DC=domain,DC=tld';
#For LDAP backend you should specify base DN including username:
#$base_dn = 'uid=%username%,ou=people,dc=domain,dc=tld';

#Specify attribute in LDAP, which will be used as group provider.
#You should add values from this attribute do dasboard "AD group"
#section, to allow LDAP users to login.
$LDAP_attribute_name='objectClass';
#$group_dn is used when you need to specify different base dn
#to list domain groups. If commented out, $base_dn will be used.
#(Active Directory only).
#$group_dn='CN=Users,DC=domain,DC=tld';
############################################################



##################Hypervisor config#########################
#Folder, where sreenshots will be stored (on hypervisor side).
#This folder must exist:
$temp_folder='/tmp';
#$backend_pass must match "password" option in [server] section
#in /usr/local/VDI/config file on hypervisor side:
$backend_pass='123456';
$ssh_user='VDI';
$ssh_key_path='/var/hyper_keys/';
$hypervisor_cmdline_path='/usr/local/VDI/';
$default_bridge='br0';
$default_imagepath='/data';
#Path, where ISO install images are located (hypervisor side):
$default_iso_path='/var/lib/libvirt/images';
$libvirt_user='root'; //user, on which libvirtd daemon runs
$libvirt_group='root'; //group, on which libvirtd daemon runs
############################################################


####################Database config#########################
$mysql_host='localhost';
$mysql_db='vdi';
$mysql_user='VDI';
$mysql_pass='123456';
############################################################

$engine='KVM';
