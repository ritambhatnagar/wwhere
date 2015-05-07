<div class="col-md-12">

    <label for="inputTitle" class="pull-left">Select Category</label>
    <select name="iCategoryId" id="iCategoryId" class="form-control select-chosen">
        <option value="0">Select Category</option>
        <?php foreach ($categoryList as $key => $value) { ?>
            <option value="<?php echo $value['iCategoryId'] ?>"><?php echo $value['vCategory'] ?></option>    
        <?php } ?>
    </select>
    <span class="help-inline" id="vNameErr"></span>                                            
</div>