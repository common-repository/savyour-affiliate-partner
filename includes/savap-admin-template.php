<div class="afflt-stngs">
<h1>Affiliate Settings</h1>
<p>Please enter your Auth Secret Key given to you by Savyour</p>
<form method="post" id="affiliate_form" class="validate" action="<?php echo admin_url('admin-ajax.php'); ?>">
    <input type="hidden" name="action" value="savap_store_data">
    <input type="hidden" id="ajax_nonce" value="<?php echo wp_create_nonce('savyour'); ?>">
    <div class="form-table">
        <div class="form-group">
            <label>Auth Secret:</label>
            <input type="text"  id="auth-secret" placeholder="eg: 4c4c4c4c4c6dsd6d6sds667ds756rv4v5v45" name="savap_auth_secret" value="<?= get_option('savap_auth_secret'); ?>"/>
        </div>
        <hr>
        <div class="form-group">
            <label>Enter Your Secret Word:<br>
                (Max 5-30 characters long)</label>
            <input  type="text" id="work-secret" placeholder="eg: fdsfds43vr433443c4cc4" name="savap_secret_work"
                <?= get_option('savap_secret_work'); ?> value="<?= get_option('savap_secret_work'); ?>"/>
        </div>
        <div class="form-group">
            <label>Your API Key:</label>
            <input class="api-key" id="savap_api_key"  readonly type="text" value="<?= get_option('savap_api_key'); ?>" />
        </div>
    </div>
    <?php if(!empty($_SESSION['success_savyour'])){?>
        <div class="alert alert-success">
            <a href="#" class="close" data-dismiss="alert" aria-label="Close" title="close">×</a>
                <?php echo $_SESSION['success_savyour']; ?>
        </div>
    <?php
        unset($_SESSION['success_savyour']);
    } ?>
    <?php if(!empty($_SESSION['error_savyour'])){?>
        <div class="alert alert-danger">
            <a href="#" class="close" data-dismiss="alert" aria-label="Close" title="close">×</a>
            <?php echo $_SESSION['error_savyour']; ?>
        </div>
        <?php
        unset($_SESSION['error_savyour']);
    } ?>
    <div class="error_form"></div>
    <?php submit_button(); ?>
</form>
</div>
<script type="text/javascript">
    window.location.pathname.includes("jazaa")&&(console.log("savyour integration"),function(){"savyour"in window||(window.savyour=function(){window.savyour.q.push(arguments)},window.savyour.q=[]);var n=(new Date).getTime();const o=document.createElement("script");o.src="https://affiliate.savyour.com.pk/sap.min.js?v="+n,o.async=!0,o.defer=!0;const e=document.getElementsByTagName("script")[0];e.parentNode.insertBefore(o,e)}(),savyour("init","586949474d417536727667584a6171493379534654513d3d"))</script>
