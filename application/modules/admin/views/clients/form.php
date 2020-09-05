<div class="row">
  	<div class="col-xl-8">
    	<form role="form" action="<?=isset($form_url) ? $form_url : '#'?>" method="POST" enctype="multipart/form-data">
			<div class="row">
				<div class="col-xl-12">
					<div class="card">
						<div class="card-header">
							<?=isset($title) ? $title : ""?>
						</div>
						<div class="card-body">
							<div class="row"> 
								<div class="col-md-12">
									<?=(isset($notification) ? (!empty($notification) ? $notification : '' ) : '') ?>
								</div>     
							</div>
							<div class="row">
								<div class="col-xl-12">
									<div class="form-group">
										<label>Code <span class="text-danger">*</span></label>
										<input name="code" class="form-control" placeholder="Code" value="<?=isset($post['code']) ? $post['code'] : ""?>">
										<span class="text-danger"><?=form_error('code')?></span>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-xl-12">
									<div class="form-group">
										<label>Name <span class="text-danger">*</span></label>
										<input name="name" class="form-control" placeholder="Name" value="<?=isset($post['name']) ? $post['name'] : ""?>">
										<span class="text-danger"><?=form_error('name')?></span>
									</div>
								</div>
							</div>
							<?php if (isset($is_update)) { ?>
							<div class="row">
								<div class="col-xl-12">
									<div class="form-control">
										<input type="checkbox" id="status" name="status" value="1" <?=isset($post["status"]) ? $post["status"] : ""?>>
										<label for="status">&nbsp; Uncheck to deactivate account.</label>
									</div>
								</div>
							</div><br>
							<?php } ?>
							<div class="row">
								<div class="col-xl-4">
									<div class="form-group">
										<button type="submit" class="btn btn-block btn-success">SAVE</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
  	</div>
</div>