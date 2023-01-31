<div class="row">
  	<div class="col-xl-12">
    	<form role="form" action="<?=isset($form_url) ? $form_url : '#'?>" method="POST" enctype="multipart/form-data">
			<div class="row">
				<div class="col-xl-12">
					<div class="card">
						<div class="card-header">
							<h1 class="h2"><?=isset($title) ? $title : ""?></h1>
						</div>
						<div class="card-body">
							<div class="row"> 
								<div class="col-md-12">
									<?=(isset($notification) ? (!empty($notification) ? $notification : '' ) : '') ?>
								</div>     
							</div>
							<div class="row">
								<div class="col-xl-6">
									<div class="form-group">
										<label>Product Title <span class="text-danger">*</span></label>
										<input name="product-title" class="form-control" placeholder="Product Title" value="<?=isset($post['product-title']) ? $post['product-title'] : ""?>">
										<span class="text-danger"><?=form_error('product-title')?></span>
									</div>
								</div>
								<div class="col-xl-3">
									<div class="form-group">
										<label>Transatel Plan <span class="text-danger">*</span></label>
										<span class="text-danger"><?=form_error('transatel-plan')?></span>
									</div>
								</div>
							</div>
							<?php if (isset($is_update)) { ?>
							<div class="row">
								<div class="col-xl-12">
									<div class="form-control">
										<input type="checkbox" id="status" name="status" value="1" <?=isset($post["status"]) ? $post["status"] : ""?>>
										<label for="status">&nbsp; Uncheck to deactivate.</label>
									</div>
								</div>
							</div><br>
							<?php } ?>
							<div class="row">
								<div class="col-xl-3">
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