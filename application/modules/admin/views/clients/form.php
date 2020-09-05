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
								<div class="col-xl-4">
									<div class="form-group">
										<label>Username <span class="text-danger">*</span></label>
										<input name="username" class="form-control" placeholder="Username" value="<?=isset($post['username']) ? $post['username'] : ""?>">
										<span class="text-danger"><?=form_error('username')?></span>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-xl-4">
									<div class="form-group">
										<label>Password <span class="text-danger">*</span></label>
										<input type="password" name="password" class="form-control" placeholder="Password" value="<?=isset($post['password']) ? $post['password'] : ""?>">
										<span class="text-danger"><?=form_error('password')?></span>
									</div>
								</div>
								<div class="col-xl-4">
									<div class="form-group">
										<label>Repeat Password <span class="text-danger">*</span></label>
										<input type="password" name="repeat-password" class="form-control" placeholder="Repeat Password" value="<?=isset($post['repeat-password']) ? $post['repeat-password'] : ""?>">
										<span class="text-danger"><?=form_error('repeat-password')?></span>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-xl-4">
									<div class="form-group">
										<label>First Name <span class="text-danger">*</span></label>
										<input name="first-name" class="form-control" placeholder="First Name" value="<?=isset($post['first-name']) ? $post['first-name'] : ""?>">
										<span class="text-danger"><?=form_error('first-name')?></span>
									</div>
								</div>
								<div class="col-xl-4">
									<div class="form-group">
										<label>Middle Name</label>
										<input name="middle-name" class="form-control" placeholder="Middle Name" value="<?=isset($post['middle-name']) ? $post['middle-name'] : ""?>">
										<span class="text-danger"><?=form_error('middle-name')?></span>
									</div>
								</div>
								<div class="col-xl-4">
									<div class="form-group">
										<label>Last Name <span class="text-danger">*</span></label>
										<input name="last-name" class="form-control" placeholder="Last Name" value="<?=isset($post['last-name']) ? $post['last-name'] : ""?>">
										<span class="text-danger"><?=form_error('last-name')?></span>
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