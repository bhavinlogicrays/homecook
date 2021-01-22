<ul class="navbar-nav">
    <?php if(config('app.ordering')): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo e(route('home')); ?>">
                <i class="ni ni-tv-2 text-primary"></i> <?php echo e(__('Dashboard')); ?>

            </a>
        </li>
        <?php if(config('app.isft')): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo e(route('orders.index')); ?>">
                <i class="ni ni-basket text-orange"></i> <?php echo e(__('Orders')); ?>

            </a>
        </li>
        <?php endif; ?>
    <?php endif; ?>

        <?php if(config('app.isft')): ?>
        <li class="nav-item">
            <a class="nav-link" href="/live">
                <i class="ni ni-basket text-success"></i> <?php echo e(__('Live Orders')); ?><div class="blob red"></div>
            </a>
        </li>
        <!-- <li class="nav-item">
            <a class="nav-link" href="<?php echo e(route('drivers.index')); ?>">
                <i class="ni ni-delivery-fast text-pink"></i> <?php echo e(__('Drivers')); ?>

            </a>
        </li> -->
        <li class="nav-item">
            <a class="nav-link" href="<?php echo e(route('clients.index')); ?>">
                <i class="ni ni-single-02 text-blue"></i> <?php echo e(__('Customers')); ?>

            </a>
        </li>
        <?php endif; ?>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo e(route('admin.restaurants.index')); ?>">
                <i class="ni ni-shop text-info"></i> <?php echo e(__('Chefs')); ?>

            </a>
        </li>
        <?php if(config('app.isft')): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo e(route('reviews.index')); ?>">
                <i class="ni ni-diamond text-info"></i> <?php echo e(__('Reviews')); ?>

            </a>
        </li>
        <?php endif; ?>
        <?php if(env('MULTI_CITY',false)): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo e(route('cities.index')); ?>">
                <i class="ni ni-building text-orange"></i> <?php echo e(__('Cities')); ?>

            </a>
        </li>
        <?php endif; ?>
        <!-- <li class="nav-item">
            <a class="nav-link" href="<?php echo e(route('pages.index')); ?>">
                <i class="ni ni-ungroup text-info"></i> <?php echo e(__('Pages')); ?>

            </a>
        </li> -->
        <?php if(env('ENABLE_PRICING',false)): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo e(route('plans.index')); ?>">
                <i class="ni ni-credit-card text-orange"></i> <?php echo e(__('Pricing plans')); ?>

            </a>
        </li>
        <?php endif; ?>
        <?php if(config('app.ordering')&&env('ENABLE_FINANCES_ADMIN',true)): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo e(route('finances.admin')); ?>">
                <i class="ni ni-money-coins text-blue"></i> <?php echo e(__('Revenue')); ?>

            </a>
        </li>
        <?php endif; ?>
        
        <?php if(env('APP_DEV',false)): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo e(route('admin.restaurant.banners.index')); ?>">
                <i class="ni ni-album-2 text-green"></i> <?php echo e(__('Banners')); ?>

            </a>
         </li>
         <?php endif; ?>
        <?php if(config('app.isqrsaas')): ?>
        <!-- <li class="nav-item">
            <?php
                $theLocaleToOpen=strtolower(env('APP_LOCALE',"EN"));
                if( strtolower(session('applocale_change')).""!=""){
                    $theLocaleToOpen=strtolower(session('applocale_change'));
                }
            ?>
            <a class="nav-link" target="_blank" href="<?php echo e(url('/admin/languages')."/".$theLocaleToOpen."/translations".(config('app.isqrsaas')?"?group=qrlanding":"")); ?>">
                <i class="ni ni-world text-orange"></i><?php echo e(__('Translations')); ?>

            </a>
        </li> -->
        <?php else: ?>
        <!-- <li class="nav-item">
            <a class="nav-link" target="_blank" href="<?php echo e(url('/admin/languages')."/".strtolower(env('APP_LOCALE',"EN"))."/translations"); ?>">
                <i class="ni ni-world text-orange"></i> <?php echo e(__('Translations')); ?>

            </a>
        </li> -->
        <?php endif; ?>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo e(route('settings.index')); ?>">
                <i class="ni ni-settings text-black"></i> <?php echo e(__('Site Settings ')); ?>

            </a>
        </li>
</ul>
<?php /**PATH /var/www/html/homecook/resources/views/layouts/navbars/menus/admin.blade.php ENDPATH**/ ?>