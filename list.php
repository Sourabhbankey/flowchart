<div class="content-wrapper">
  <div class="modal fade bs-example-modal-new" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id="myModal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="gridSystemModalLabel" style="text-align: center;">Welcome <span><?php echo $name; ?></span></h4>
        </div>
        <div class="modal-body">
          <div class="body-message">
            <p style="text-align: center;font-size: 20px;color: #e32020;">Please complete the missing field click here on view button</p>
            <?php
            $records = $this->session->userdata('data');

            if (!empty($records)) {
              foreach ($records as $branch) {
            ?>
                <div class="row modrow-cls">
                <!-- <img class="loader1" style="display: none;"src="<?php //echo base_url() ?>assets/images/Rolling.svg"> -->
                  <div class="col-md-3">
                    <span class="label label-success1"> <?php echo $branch->franchiseNumber ?></span>
                  </div>
                  <div class="col-md-3">
                    <span class="label label-success1" style="margin-left: -37px;"><?php echo $branch->applicantName ?></span>
                  </div>
                  <div class="col-md-3">
                    <span class="label label-success1"><?php echo $branch->mobile ?></span>
                  </div>
                  <div class="col-md-3">
                    <a class="btn btn-sm btn-info1 modviewbtn" style="margin-top: 3px;" href="<?php echo base_url() . 'branches/edit/' . $branch->branchesId; ?>">View <i class="fa fa-eye"></i></a>
                  </div>
                </div>


            <?php
              }
            }
            ?>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade bs-example-modal-new" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id="myModal2">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="gridSystemModalLabel">Welcome <span><?php echo $name; ?></span></h4>
        </div>
        <div class="modal-body">
          <div class="body-message">
            <p>Weldone <span><?php echo $name; ?></span></p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  <!-- Content Header (Page header) -->
  <?php if ($role == 25) { ?>
    <section class="content-header">
    <h1>
      <i class="fa fa-tachometer" aria-hidden="true"></i> Franchise Dashboard
      <small>Control panel</small>
    </h1>
  </section>
  <?php }else{ ?>  
  <section class="content-header">
    <h1>
      <i class="fa fa-tachometer" aria-hidden="true"></i> Dashboard
      <small>Control panel</small>
    </h1>
  </section>


</div>
<style type="text/css">
  .modrow-cls {
    /*background-color: #E3F2FD;*/
    padding: 10px;
    text-align: center;
    border-bottom: 2px solid #fff;
    /*height: auto; */
    background-color: red;
    background-image: repeating-linear-gradient(red, yellow 25%, green 100%);
  }

  .label-success1 {
    /*color: #545454;*/
    color: #1d1d1d;
    font-size: 14px;
  }

  .modviewbtn {
    padding: 5px 10px 5px 10px;
    font-size: 20px;
    text-align: center;
    cursor: pointer;
    outline: none;
    color: #fff;
    background-color: #f4511e;
    border: none;
    border-radius: 15px;
    box-shadow: 0 6px #999;
  }

  .modviewbtn:active {
    background-color: #f4511e;
    box-shadow: 0 5px #666;
    transform: translateY(4px);
  }

  .modviewbtn:hover {
    color: #fff;
    text-decoration: none;
    background: #f93b00;
    box-shadow: 0 6px #e5e4e4;
  }

  .modal-content {
    border: 4px solid #f93b00;
    border-radius: 5px;
  }
  .frnewbtn a button:hover {
      background: #3c8dbc;
      color: #fff;
      border: 2px solid #f39c12;
  }
.frnewbtn a button{
    padding: 5px;
    border-radius: 5px;
    border: 2px solid #f39c12;
    margin: 4px;
    background: #fff;
}
/*---Progress-Bar---*/
.progress {
    background-color: #f16868;
    border-radius: 50px;
    height: 15px;
    margin-bottom: 5px;
}
.progress-bar {
    height: 97%;
    background-color: #238f2a;
    line-height: 14px;
}
.progress-bar-striped, .progress-striped .progress-bar {
     background-size: 10px 10px;
}
</style>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/common.js" charset="utf-8"></script>
<script type="text/javascript">
  
  jQuery(document).ready(function() {
    $(".loderr").hide();
    var adminID = "<?php echo $is_admin; ?>";
    var modelshow = "<?php echo $this->session->userdata('modelShow'); ?>";
    var emptyRow = "<?php echo $this->session->userdata('emptyRow'); ?>";
    var roleId = "<?php echo $role; ?>";
    if (adminID != 1 && roleId != 25) {
      jQuery('#myModal').modal('show');
      jQuery('#myModal2').modal('hide');
    }else if (adminID != 1) {
      jQuery('#myModal2').modal('show');
      jQuery('#myModal').modal('hide');
    }
    if (adminID == 1) {
      jQuery('.textHide').modal('hide');
    }

  });
  Dashboard()
  function Dashboard() {
    var userId  = <?php echo $userId ?>;
    var roleId  = <?php echo $role ?>;
    var isAdmin = <?php echo $is_admin ?>;
    $.ajax({
      url: "<?php echo base_url('login/getBranchData'); ?>",
      method: "POST",
      data: {
        userId: userId,
        roleId: roleId,
        isAdmin: isAdmin 
      },
      beforeSend: function() {
          $("#DashboardLoader").show();
      },
      success: function(data) {
          $("#DashboardData").html(data);
          $("#DashboardLoader").hide();
          $("#fromRa").hide();
          $("#toRa").hide();
          $("#to2").hide();
          $("#getRangeDataService").hide();
      },
      error: function(data) {
          $("#SubmitLoader").hide();
      }
    });
  }
</script>