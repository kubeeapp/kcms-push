<?php

// If this file is called directly, abort.
if ( ! defined('WPINC')) {
    die;
}

?><h2>WP Pusher Repositories</h2>

<hr>
<br>

<?php if (count($repositories) < 1) { ?>
    <div>
        <p>No repositories installed with WP Pusher yet.</p>
    </div>
<?php } else { ?>
    <table class="wp-list-table widefat fixed striped posts">
        <thead>
            <tr>
                <th class="manage-column column-title column-primary">Repository</th>
                <th class="manage-column">Source</th>
                <th class="manage-column">Type</th>
                <th class="manage-column">Branch</th>
                <th class="manage-column">Push-to-Deploy</th>
                <th class="manage-column">Subdirectory</th>
                <th class="manage-column">Action</th>
            </tr>
        </thead>
        <tbody id="the-list">
            <?php if (count($repositories) < 1) { ?>
                <tr>
                    <td></td>
                    <td></td>
                </tr>
            <?php } ?>
            <?php foreach ($repositories as $repository) { ?>
                <tr>
                    <td>
                        <a 
                            href="?page=wppusher-<?php echo $repository->type ?>s&package=<?php echo $repository->type === 'theme' ? urlencode($repository->stylesheet) : urlencode($repository->file); ?>">
                            <?php echo $repository->name ?>
                        </a>
                    </td>
                    <td>
                        <i class="fa <?php echo getHostIcon($repository->host); ?>"></i>
                        <span>
                            <?php echo $repository->repository; ?>
                        </span>
                    </td>
                    <td>
                        <a href="?page=wppusher-<?php echo $repository->type ?>s-create">
                            <?php echo ucfirst($repository->type); ?>
                        </a>
                    </td>
                    <td>
                        <?php echo $repository->repository->getBranch(); ?>
                    </td>
                    <td>
                        <?php echo $repository->pushToDeploy ? 'Enabled' : 'Disabled' ?>
                    </td>
                    <td>
                        <?php echo $repository->hasSubdirectory() ? $repository->getSubdirectory() : 'N/A' ; ?>
                    </td>
                    <td>
                        <form action="" method="POST">
                            <?php wp_nonce_field("update-repository"); ?>
                            <input type="hidden" name="wppusher[type]" value="<?php echo $repository->type ?>">
                            <input type="hidden" name="wppusher[action]" value="update-repository">
                            <input type="hidden" name="wppusher[repository]" value="<?php echo $repository->repository; ?>">
                            <?php if ($repository->type === 'theme') { ?>
                                <input type="hidden" name="wppusher[stylesheet]" value="<?php echo $repository->stylesheet; ?>">
                            <?php } ?>
                            <?php if ($repository->type === 'plugin') { ?>
                                <input type="hidden" name="wppusher[file]" value="<?php echo $repository->file; ?>">
                            <?php } ?>
                            <button type="submit" class="button button-secondary button-update-package"><i class="fa fa-refresh"></i>&nbsp; Update</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } ?>

<br>

<div>
    <a href="?page=wppusher-plugins-create" class="button button-primary">
        Install plugin
    </a>
    <a href="?page=wppusher-themes-create" class="button button-primary">
        Install theme
    </a>
</div>

<br>
