<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Make thing clear
 *
 * @var JForm   $form       The form instance for render the section
 * @var string  $basegroup  The base group name
 * @var string  $group      Current group name
 * @var array   $buttons    Array of the buttons that will be rendered
 */
extract($displayData);

?>

<div class="row subform-repeatable-group bg-faded py-0 px-25 my-20 ml-0 mr-0" data-base-name="<?php echo $basegroup; ?>" data-group="<?php echo $group; ?>">
	<?php if (!empty($buttons)) : ?>
	<div class="btn-toolbar text-right">
		<?php if (!empty($buttons['add'])) : ?><a class="group-add btn btn-sm button btn-success"><span class="fa fa-2x fa-plus"></span> </a><?php endif; ?>
		<?php if (!empty($buttons['remove'])) : ?><a class="group-remove btn btn-sm button btn-danger"><span class="fa fa-2x fa-trash"></span> </a><?php endif; ?>
		<?php if (!empty($buttons['move'])) : ?><a class="group-move btn btn-sm button btn-primary"><span class="fa fa-2x fa-move"></span> </a><?php endif; ?>
	</div>
	<?php endif; ?>

<?php foreach ($form->getGroup('') as $key => $field) : ?>
	<?php
		$form = str_replace('control-group', 'col-xs-12 col-sm-6 form-group form_'.$field->class, $field->renderField(array('hiddenLabel' => false)));
		$col = str_replace('control-label', 'col-xs-12 col-md-3', $form);
		$col = str_replace('controls', 'col-xs-12 col-md-8', $col);
		echo str_replace('form-label', 'col-xs-12', $col);
	?>
<?php endforeach; ?>
</div>
