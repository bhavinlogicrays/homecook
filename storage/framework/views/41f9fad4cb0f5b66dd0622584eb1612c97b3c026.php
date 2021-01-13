<footer class="footer">
    <div class="container">
      <div class="row align-items-center justify-content-md-between">
        <div class="col-md-6">
          <div class="copyright">
            &copy; 2020 <a href="" target="_blank"><?php echo e(config('global.site_name', 'mResto')); ?></a>.
          </div>
        </div>
        <div class="col-md-6">
          <ul id="footer-pages" class="nav nav-footer justify-content-end">
            <li v-for="page in pages" class="nav-item" v-cloak>
                <a :href="'/pages/' + page.id" class="nav-link">{{ page.title }}</a>
            </li>

            <?php if(!env('SINGLE_MODE',false)&&env('RESTAURANT_LINK_REGISTER_POSITION','footer')=="footer"): ?>
            <li class="nav-item">
              <a  target="_blank" class="button nav-link nav-link-icon" href="<?php echo e(route('newrestaurant.register')); ?>"><?php echo e(__(env('RESTAURANT_LINK_REGISTER_TITLE',"Register your restaurant"))); ?></a>
            </li>
          <?php endif; ?>
          <?php if(config('app.isft')&&env('DRIVER_LINK_REGISTER_POSITION','footer')=="footer"): ?>
          <li class="nav-item">
              <a target="_blank" class="button nav-link nav-link-icon" href="<?php echo e(route('driver.register')); ?>"><?php echo e(__(env('DRIVER_LINK_REGISTER_TITLE',"Register as Driver"))); ?></a>
            </li>
            <?php endif; ?>

          </ul>
        </div>
      </div>
    </div>
  </footer>
<?php /**PATH /var/www/html/homecook/resources/views/layouts/footers/front.blade.php ENDPATH**/ ?>