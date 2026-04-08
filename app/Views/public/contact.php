<section class="inner-hero"><div class="container"><span class="eyebrow">Contact</span><h1>Reach the team for websites, apps, hosting, marketplace products, and support.</h1></div></section>
<section class="section-space">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="glass-panel h-100">
                    <h3>Contact details</h3>
                    <div class="stack-list compact">
                        <li><?= e($settings['contact_phone'] ?? config('app.company_phone')) ?></li>
                        <li><?= e($settings['contact_email'] ?? config('app.company_email')) ?></li>
                        <li><?= e($settings['contact_address'] ?? config('app.company_address')) ?></li>
                    </div>
                    <a class="btn btn-primary rounded-pill mt-3" href="https://wa.me/<?= e($settings['company_whatsapp'] ?? config('app.company_whatsapp')) ?>" target="_blank">Chat on WhatsApp</a>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="glass-panel h-100">
                    <h3>Project inquiry</h3>
                    <form class="stack-form labeled-form" method="post" action="<?= route_url('/contact') ?>">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Full name</label><input class="form-control" name="name" placeholder="Full Name"></div>
                            <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" name="email" placeholder="Email"></div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" placeholder="Phone"></div>
                            <div class="col-md-6"><label class="form-label">Company</label><input class="form-control" name="company" placeholder="Company"></div>
                        </div>
                        <div><label class="form-label">Service interest</label><input class="form-control" name="service_interest" placeholder="Service interest"></div>
                        <div><label class="form-label">Requirement details</label><textarea class="form-control" name="message" rows="5" placeholder="Tell us what you want to build"></textarea></div>
                        <button class="btn btn-outline-light rounded-pill" type="submit">Submit Inquiry</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
