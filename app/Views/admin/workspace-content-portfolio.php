<div class="row g-4">
    <div class="col-xl-5">
        <div class="dash-card">
            <div class="card-title-row"><h4>Create Portfolio Project</h4><span>Case studies</span></div>
            <form class="stack-form mt-3" method="post" action="<?= route_url('/admin/portfolio') ?>">
                <input class="form-control" name="title" placeholder="Project title" required>
                <input class="form-control" name="slug" placeholder="Slug">
                <input class="form-control" name="category" placeholder="Category">
                <input class="form-control" name="client_name" placeholder="Client name">
                <textarea class="form-control" name="summary" rows="3" placeholder="Summary"></textarea>
                <textarea class="form-control" name="tech_stack" rows="3" placeholder="Tech stack"></textarea>
                <div class="row g-3">
                    <div class="col-md-6"><input class="form-control" name="sort_order" value="0" placeholder="Sort order"></div>
                    <div class="col-md-6 d-flex align-items-center"><label class="small-text mb-0"><input class="form-check-input me-2" type="checkbox" name="is_featured" value="1"> Featured</label></div>
                </div>
                <button class="btn btn-primary" type="submit">Add Project</button>
            </form>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="dash-card">
            <div class="card-title-row"><h4>Portfolio Projects</h4><span>Frontend showcase</span></div>
            <?php foreach ($portfolioProjects as $project): ?>
                <form class="dash-form-block" method="post" action="<?= route_url('/admin/portfolio/update') ?>">
                    <input type="hidden" name="project_id" value="<?= e((string) $project['id']) ?>">
                    <div class="row g-3">
                        <div class="col-md-4"><input class="form-control" name="title" value="<?= e($project['title']) ?>"></div>
                        <div class="col-md-4"><input class="form-control" name="slug" value="<?= e($project['slug']) ?>"></div>
                        <div class="col-md-4"><input class="form-control" name="category" value="<?= e($project['category']) ?>"></div>
                        <div class="col-12"><textarea class="form-control" name="summary" rows="3"><?= e($project['summary']) ?></textarea></div>
                    </div>
                    <div class="toolbar-actions mt-3">
                        <button class="btn btn-primary" type="submit">Save</button>
                        <button class="btn btn-danger" type="submit" formaction="<?= route_url('/admin/portfolio/delete') ?>">Delete</button>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Team Directory</h4><span>About page roster</span></div>
            <?php foreach ($teamMembers as $member): ?>
                <div class="list-row">
                    <div>
                        <strong><?= e($member['name']) ?></strong>
                        <span><?= e($member['role']) ?><?= ! empty($member['email']) ? ' | ' . e($member['email']) : '' ?></span>
                    </div>
                    <span class="badge-soft">team</span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
