<?php
//############################################################################################
function HTML5Buttons(){
    include (dirname(__FILE__) . '/../../../functions/config.php');
    require_once(dirname(__FILE__) . '/../../../functions/functions.php');
    slash_vars();
    if (!check_client_session()){
        exit;
    }
    $userid=$_SESSION['userid'];
    $username=$_SESSION['username'];
    echo'<div class="container">
        <div class="row">
        <div class="alert alert-warning hidden" id="warningbox">
        </div>';
        $last_reload=get_SQL_array ("SELECT id FROM config WHERE name='lastreload' AND valuedate > DATE_SUB(NOW(), INTERVAL 30 SECOND) LIMIT 1"); //if there was no reload of VM list in 30 seconds, initiate reload.

        if (!isset($last_reload[0]['id'])){
            add_SQL_line("INSERT INTO config (name,valuedate) VALUES ('lastreload',NOW()) ON DUPLICATE KEY UPDATE valuedate=NOW()");
            reload_vm_info();
        }
        if ($_SESSION['ad_user']=='yes'||$_SESSION['ad_user']=='LDAP'){
            $group_array=$_SESSION['group_array'];
            if(!empty($group_array)){
                $pool_reply=get_SQL_array("SELECT DISTINCT(pool.id), pool.name FROM poolmap_ad  LEFT JOIN pool ON poolmap_ad.poolid=pool.id LEFT JOIN ad_groups ON poolmap_ad.groupid=ad_groups.id WHERE ad_groups.name IN ($group_array)");
            }
        }
        else
            $pool_reply=get_SQL_array("SELECT pool.id, pool.name FROM poolmap  LEFT JOIN pool ON poolmap.poolid=pool.id WHERE clientid='$userid'");
        
        $x=0;
        while ($x<sizeof($pool_reply)){
            $vm_count=get_SQL_array("SELECT COUNT(*) FROM poolmap_vm LEFT JOIN vms ON poolmap_vm.vmid=vms.id LEFT JOIN hypervisors ON vms.hypervisor=hypervisors.id WHERE poolmap_vm.poolid='{$pool_reply[$x]['id']}' AND vms.maintenance!='true' AND vms.locked='false' AND hypervisors.maintenance!=1");
            $vm_count_available=get_SQL_array("SELECT COUNT(*) FROM poolmap_vm LEFT JOIN vms ON poolmap_vm.vmid=vms.id LEFT JOIN hypervisors ON vms.hypervisor=hypervisors.id  WHERE poolmap_vm.poolid='{$pool_reply[$x]['id']}' AND vms.maintenance='false' AND vms.locked!='true' AND hypervisors.maintenance!=1 AND vms.lastused < DATE_SUB(NOW(), INTERVAL '$return_to_pool_after' MINUTE) ");
            $already_have=get_SQL_array("SELECT COUNT(*) FROM poolmap_vm LEFT JOIN vms ON poolmap_vm.vmid=vms.id LEFT JOIN hypervisors ON vms.hypervisor=hypervisors.id  WHERE poolmap_vm.poolid='{$pool_reply[$x]['id']}'AND vms.maintenance!='true' AND hypervisors.maintenance!=1 AND vms.clientid='$userid' AND vms.lastused > DATE_SUB(NOW(), INTERVAL '$return_to_pool_after' MINUTE)");
            $vm_image="text-warning";
            $provided_vm=array();
            $provided_vm[0]['name']="none";
            if ($already_have[0][0]==1){
                $vm_image="text-success";
                $provided_vm=get_SQL_array("SELECT vms.name,vms.state,vms.id FROM poolmap_vm LEFT JOIN vms ON poolmap_vm.vmid=vms.id LEFT JOIN hypervisors ON vms.hypervisor=hypervisors.id  WHERE poolmap_vm.poolid='{$pool_reply[$x]['id']}'AND vms.maintenance!='true' AND hypervisors.maintenance!=1 AND vms.clientid='$userid' AND vms.lastused > DATE_SUB(NOW(), INTERVAL '$return_to_pool_after' MINUTE)");
            }
            else if ($vm_count_available[0][0]==0)
                $vm_image="text-muted";
            if (!isset($provided_vm[0]['state']))
                $provided_vm[0]['state']='';
            $pm_icons="";
            /*if ($provided_vm[0]['state']=='running'||$provided_vm[0]['state']=='pmsuspended'||$provided_vm[0]['state']=='paused'){
                $pm_icons='<a href="#" class="shutdown"  id="' . $provided_vm[0]['id'] . '"><i class="pull-left fa fa-stop-circle-o text-danger" title="' . _("Shutdown machine") . '"></i></a>';
                $pm_icons=$pm_icons.'<a href="#" class="terminate"  id="' . $provided_vm[0]['id'] . '"><i class="pull-left fa fa-times-circle-o text-danger" title="' . ("Terminate machine") . '"></i></a>';
            }*/
            $member=get_SQL_array("SELECT COUNT(*) FROM poolmap where poolid='{$pool_reply[$x]['id']}'");
            echo'<div class="col-md-12">';
            echo '<div class="row text-info">
                <div class="panel panel-default">
                <div class="panel-heading">
                <div class="row">
                <div class="col-xs-6">
                </div>



                <div class="col-xs-8">
                <small>Client :' . $member[0][0] . '</small>
                </div>
                </div>
                <div class="row">
                <div class="text-center">
                    <a href="#" id="' . $pool_reply[$x]['id'] . '" class="pools1">
                    <button>
                    <span class="fa-stack fa-4x">
                        <i class="fa fa-square-o fa-stack-2x"></i>
                        <i class="fa fa-power-off fa-stack-1x"></i>
                    </span>
                    </button>
                    </a>
                </div>
                </div>
                <div class="row text-center">
                    <div>
                        <span>Pool name: ' . $pool_reply[$x]['name'] . '</span>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <span class="pull-left"><small>Pool size: ' . $vm_count[0][0] . '</small></span>
                <span class="pull-right"><small>Available: ' . $vm_count_available[0][0] . '</small></span>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>'."\n";
            
            
    $sql_reply=get_SQL_array("SELECT * FROM hypervisors ORDER BY name,ip ASC");

    $vms_query=get_SQL_array("SELECT vms.id,vms.name,vms.hypervisor,vms.machine_type,vms.source_volume,vms.snapshot,vms.maintenance,vms.filecopy,vms.state,vms.os_type,vms.locked,vms.lastused,clients.username, vms_tmp.name AS sourcename  FROM vms LEFT JOIN vms AS vms_tmp ON vms.source_volume=vms_tmp.id LEFT JOIN clients ON clients.id=vms.clientid WHERE vms.hypervisor='{$sql_reply[$x]['id']}' AND vms.machine_type <> 'vdimachine' ORDER BY vms.name");

    if ($vms_query[$y]['machine_type']=='initialmachine'){

        // Lay tat ca VDI machine cua initial machine
        $VDI_query=get_SQL_array("SELECT vms.id,vms.name,vms.hypervisor,vms.machine_type,vms.source_volume,vms.snapshot,vms.maintenance,vms.filecopy,vms.state,vms.os_type,vms.lastused,clients.username,vms_tmp.name AS sourcename  FROM vms LEFT JOIN vms AS vms_tmp ON vms.source_volume=vms_tmp.id LEFT JOIN clients ON clients.id=vms.clientid WHERE vms.source_volume='{$vms_query[$y]['id']}' AND vms.machine_type = 'vdimachine' ORDER BY vms.name");
    }



    // lay tat ca cac VM trong pool
    $vmmmm=get_SQL_array("SELECT vms.id,vms.name,vms.hypervisor,vms.state,vms.clientid,clients.username FROM poolmap_vm LEFT JOIN vms ON poolmap_vm.vmid=vms.id LEFT JOIN hypervisors ON vms.hypervisor=hypervisors.id LEFT JOIN clients ON clients.id=vms.clientid WHERE poolmap_vm.poolid='{$pool_reply[$x]['id']}' AND vms.maintenance!='true' AND vms.locked='false' AND hypervisors.maintenance!=1;");

        $z=0;
        $statusss= '';
        $used_by= '';
        while ($z<sizeof($vmmmm)) {
            if ($vmmmm[$z]['state']==='running'){
                $pm_icons='<a href="#" class="shutdown"  id="' . $vmmmm[$z]['id'] . '"><i class="pull-left fa fa-stop-circle-o text-danger" title="' . _("Shutdown machine") . '"></i></a>';
                $pm_icons=$pm_icons.'<a href="#" class="terminate"  id="' . $vmmmm[$z]['id'] . '"><i class="pull-left fa fa-times-circle-o text-danger" title="' . ("Terminate machine") . '"></i></a>';
            }else{
                $pm_icons='';
            }
            
            if ($vmmmm[$z]['state']==='shut'){
                    $statusss='<i class="text-danger">' . _("Shutoff") . '</i>';
                } elseif ($vmmmm[$z]['state']==='paused'){
                        $statusss='<i class="text-warning">' . _("Paused") . '</i>';
                    } elseif ($vmmmm[$z]['state']==='pmsuspended') {
                            $statusss='<i class="text-warning">' . _("Suspended") . '</i>';
                        } elseif ($vmmmm[$z]['state']==='running') {
                                $statusss='<i class="text-success">' . _("Running") . '</i>';
                            } else {
                                    $statusss='<i class="text-muted">' . _("Unknown") . '</i>';
                                }




            /*if (strtotime($VDI_query[$z]['lastused']) > strtotime("-" . $return_to_pool_after . " minutes"))
                    $used_by=$VDI_query[$z]['username'];
                else
                    $used_by=_("Nobody");*/

            $used_by=$vmmmm[$z]['username'];
    
            echo'<div class="col-md-2">';
            echo '<div class="row text-info">
                <div class="panel panel-default">
                <div class="panel-heading">
                <div class="row">
                <div class="col-xs-4">
                <small>' . $pm_icons  . '</small>
                </div>



                <div class="col-xs-8">
                <small>' . $provided_vm[0]['name'] . '</small>
                </div>
                </div>
                <div class="row">
                <div class="text-center">
                    <a href="#" id="' . $vmmmm[$z]['id'] . '" class="pools">
                    <span class="fa-stack fa-4x">
                        <i class="fa fa-square-o fa-stack-2x"></i>
                        <i class="fa fa-power-off fa-stack-1x ' . $vm_image . '"></i>
                    </span>
                    </a>
                </div>
                </div>
                <div class="row text-center">
                    <div>
                        <span>' . $vmmmm[$z]['name'] . '</span>
                    </div>
                </div>
            </div>

            <div class="panel-footer">
                <!-- <span class="pull-left"><small>Use by: ' . $used_by . '</small></span> -->
                <span class="pull-right"><small>Status: ' . $statusss . '</small></span>
                <div class="clearfix"></div>
            </div>
           
        </div>
    </div>
</div>'."\n";

        ++$z;
        }
        

        ++$x;

        if ((($x % 4) / 4)==0)//number of columns
    echo '</div>' . "\n". '<div class="row">' . "\n";
    }
    echo '</div>
        </div>';
}
//############################################################################################
