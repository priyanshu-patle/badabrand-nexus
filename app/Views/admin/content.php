<div class="row g-4">
    <div class="col-12">
        <div class="dash-card">
            <div class="card-title-row">
                <div>
                    <h4>Content Hub</h4>
                    <p class="mb-0 text-muted">Manage the remaining public content modules buyers expect in a production-ready script.</p>
                </div>
                <span class="badge-soft">Pages, blog, FAQ, careers, portfolio, team</span>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="dash-card">
            <div class="card-title-row"><h4>Public Legal Pages</h4><span>Privacy and terms editor</span></div>
            <?php foreach ($pages as $page): ?>
                <form class="dash-form-block" method="post" action="<?= route_url('/admin/pages/update') ?>">
                    <input type="hidden" name="page_id" value="<?= e((string) $page['id']) ?>">
                    <div class="row g-3">
                        <div class="col-md-6"><input class="form-control" name="title" value="<?= e($page['title']) ?>" placeholder="Page title"></div>
                        <div class="col-md-6"><input class="form-control" name="slug" value="<?= e($page['slug']) ?>" placeholder="Slug"></div>
                        <div class="col-12"><textarea class="form-control" name="excerpt" rows="2" placeholder="Short intro"><?= e($page['excerpt']) ?></textarea></div>
                        <div class="col-12"><textarea class="form-control" name="content" rows="6" placeholder="Page content"><?= e($page['content']) ?></textarea></div>
                        <div class="col-md-6"><input class="form-control" name="meta_title" value="<?= e($page['meta_title']) ?>" placeholder="Meta title"></div>
                        <div class="col-md-6"><input class="form-control" name="meta_description" value="<?= e($page['meta_description']) ?>" placeholder="Meta description"></div>
                    </div>
                    <div class="admin-tools mt-3">
                        <span class="badge-soft"><?= e($page['slug']) ?></span>
                        <div class="toolbar-actions">
                            <button class="btn btn-primary" type="submit">Save Page</button>
                        </div>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="dash-card">
            <div class="card-title-row"><h4>Add Blog Post</h4><span>SEO publishing module</span></div>
            <form class="stack-form" method="post" action="<?= route_url('/admin/blogs') ?>">
                <input class="form-control" name="title" placeholder="Post title" required>
                <div class="row g-3">
                    <div class="col-md-6"><input class="form-control" name="slug" placeholder="Slug (optional)"></div>
                    <div class="col-md-6"><input class="form-control" name="category" placeholder="Category"></div>
                </div>
                <textarea class="form-control" name="excerpt" rows="2" placeholder="Short excerpt"></textarea>
                <textarea class="form-control" name="content" rows="6" placeholder="Blog content"></textarea>
                <div class="row g-3">
                    <div class="col-md-6"><input class="form-control" name="meta_title" placeholder="Meta title"></div>
                    <div class="col-md-6"><input class="form-control" name="meta_description" placeholder="Meta description"></div>
                </div>
                <select class="form-select" name="status">
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                </select>
                <button class="btn btn-primary rounded-pill" type="submit">Add Blog Post</button>
            </form>
        </div>
    </div>

    <div class="col-12">
        <div class="dash-card">
            <div class="card-title-row"><h4>Published Blog Posts</h4><span>Editable content records</span></div>
            <?php foreach ($blogs as $post): ?>
                <form class="dash-form-block" method="post" action="<?= route_url('/admin/blogs/update') ?>">
                    <input type="hidden" name="blog_id" value="<?= e((string) $post['id']) ?>">
                    <div class="row g-3">
                        <div class="col-md-4"><input class="form-control" name="title" value="<?= e($post['title']) ?>" placeholder="Title"></div>
                        <div class="col-md-3"><input class="form-control" name="slug" value="<?= e($post['slug']) ?>" placeholder="Slug"></div>
                        <div class="col-md-3"><input class="form-control" name="category" value="<?= e($post['category']) ?>" placeholder="Category"></div>
                        <div class="col-md-2">
                            <select class="form-select" name="status">
                                <?php foreach (['draft', 'published'] as $status): ?>
                                    <option value="<?= e($status) ?>" <?= ($post['status'] ?? 'draft') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12"><textarea class="form-control" name="excerpt" rows="2" placeholder="Excerpt"><?= e($post['excerpt']) ?></textarea></div>
                        <div class="col-12"><textarea class="form-control" name="content" rows="5" placeholder="Content"><?= e($post['content']) ?></textarea></div>
                        <div class="col-md-6"><input class="form-control" name="meta_title" value="<?= e($post['meta_title']) ?>" placeholder="Meta title"></div>
                        <div class="col-md-6"><input class="form-control" name="meta_description" value="<?= e($post['meta_description']) ?>" placeholder="Meta description"></div>
                    </div>
                    <div class="admin-tools mt-3">
                        <span class="badge-soft"><?= e($post['slug']) ?></span>
                        <div class="toolbar-actions">
                            <button class="btn btn-primary" type="submit">Save</button>
                            <button class="btn btn-danger" type="submit" formaction="<?= route_url('/admin/blogs/delete') ?>" onclick="return confirm('Delete this blog post?')">Delete</button>
                        </div>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="dash-card">
            <div class="card-title-row"><h4>Add FAQ</h4><span>Support-ready answers</span></div>
            <form class="stack-form" method="post" action="<?= route_url('/admin/faqs') ?>">
                <input class="form-control" name="question" placeholder="Question" required>
                <textarea class="form-control" name="answer" rows="4" placeholder="Answer"></textarea>
                <input class="form-control" name="sort_order" value="0" placeholder="Sort order">
                <button class="btn btn-primary rounded-pill" type="submit">Add FAQ</button>
            </form>
        </div>
        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Add Career Role</h4><span>Hiring board</span></div>
            <form class="stack-form" method="post" action="<?= route_url('/admin/careers') ?>">
                <input class="form-control" name="title" placeholder="Role title" required>
                <textarea class="form-control" name="summary" rows="3" placeholder="Role summary"></textarea>
                <div class="row g-3">
                    <div class="col-md-6"><input class="form-control" name="location" placeholder="Location"></div>
                    <div class="col-md-6"><input class="form-control" name="employment_type" placeholder="Employment type"></div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <select class="form-select" name="status">
                            <option value="open">open</option>
                            <option value="closed">closed</option>
                        </select>
                    </div>
                    <div class="col-md-6"><input class="form-control" name="sort_order" value="0" placeholder="Sort order"></div>
                </div>
                <button class="btn btn-primary rounded-pill" type="submit">Add Career Role</button>
            </form>
        </div>
        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Add Team Member</h4><span>About page roster</span></div>
            <form class="stack-form" method="post" action="<?= route_url('/admin/team') ?>">
                <input class="form-control" name="name" placeholder="Team member name" required>
                <input class="form-control" name="role" placeholder="Role or designation">
                <input class="form-control" name="email" placeholder="Email">
                <button class="btn btn-primary rounded-pill" type="submit">Add Team Member</button>
            </form>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="dash-card">
            <div class="card-title-row"><h4>FAQ Records</h4><span>Public accordion content</span></div>
            <?php foreach ($faqItems as $item): ?>
                <form class="dash-form-block" method="post" action="<?= route_url('/admin/faqs/update') ?>">
                    <input type="hidden" name="faq_id" value="<?= e((string) $item['id']) ?>">
                    <div class="row g-3">
                        <div class="col-md-9"><input class="form-control" name="question" value="<?= e($item['question']) ?>" placeholder="Question"></div>
                        <div class="col-md-3"><input class="form-control" name="sort_order" value="<?= e((string) $item['sort_order']) ?>" placeholder="Sort order"></div>
                        <div class="col-12"><textarea class="form-control" name="answer" rows="3" placeholder="Answer"><?= e($item['answer']) ?></textarea></div>
                    </div>
                    <div class="admin-tools mt-3">
                        <div class="toolbar-actions">
                            <button class="btn btn-primary" type="submit">Save</button>
                            <button class="btn btn-danger" type="submit" formaction="<?= route_url('/admin/faqs/delete') ?>" onclick="return confirm('Delete this FAQ?')">Delete</button>
                        </div>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>

        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Career Roles</h4><span>Public hiring module</span></div>
            <?php foreach ($jobs as $job): ?>
                <form class="dash-form-block" method="post" action="<?= route_url('/admin/careers/update') ?>">
                    <input type="hidden" name="career_id" value="<?= e((string) $job['id']) ?>">
                    <div class="row g-3">
                        <div class="col-md-4"><input class="form-control" name="title" value="<?= e($job['title']) ?>" placeholder="Title"></div>
                        <div class="col-md-3"><input class="form-control" name="location" value="<?= e($job['location']) ?>" placeholder="Location"></div>
                        <div class="col-md-3"><input class="form-control" name="employment_type" value="<?= e($job['employment_type']) ?>" placeholder="Employment type"></div>
                        <div class="col-md-2">
                            <select class="form-select" name="status">
                                <?php foreach (['open', 'closed'] as $status): ?>
                                    <option value="<?= e($status) ?>" <?= ($job['status'] ?? 'open') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12"><textarea class="form-control" name="summary" rows="3" placeholder="Summary"><?= e($job['summary']) ?></textarea></div>
                        <div class="col-md-3"><input class="form-control" name="sort_order" value="<?= e((string) $job['sort_order']) ?>" placeholder="Sort order"></div>
                    </div>
                    <div class="admin-tools mt-3">
                        <div class="toolbar-actions">
                            <button class="btn btn-primary" type="submit">Save</button>
                            <button class="btn btn-danger" type="submit" formaction="<?= route_url('/admin/careers/delete') ?>" onclick="return confirm('Delete this role?')">Delete</button>
                        </div>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>

        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Team Members</h4><span>About page people module</span></div>
            <?php foreach ($teamMembers as $member): ?>
                <form class="dash-form-block" method="post" action="<?= route_url('/admin/team/update') ?>">
                    <input type="hidden" name="team_id" value="<?= e((string) $member['id']) ?>">
                    <div class="row g-3">
                        <div class="col-md-4"><input class="form-control" name="name" value="<?= e($member['name']) ?>" placeholder="Name"></div>
                        <div class="col-md-4"><input class="form-control" name="role" value="<?= e($member['role']) ?>" placeholder="Role"></div>
                        <div class="col-md-4"><input class="form-control" name="email" value="<?= e($member['email']) ?>" placeholder="Email"></div>
                    </div>
                    <div class="admin-tools mt-3">
                        <div class="toolbar-actions">
                            <button class="btn btn-primary" type="submit">Save</button>
                            <button class="btn btn-danger" type="submit" formaction="<?= route_url('/admin/team/delete') ?>" onclick="return confirm('Delete this team member?')">Delete</button>
                        </div>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="dash-card">
            <div class="card-title-row"><h4>Add Portfolio Project</h4><span>Case study manager</span></div>
            <form class="stack-form" method="post" action="<?= route_url('/admin/portfolio') ?>">
                <input class="form-control" name="title" placeholder="Project title" required>
                <div class="row g-3">
                    <div class="col-md-6"><input class="form-control" name="slug" placeholder="Slug (optional)"></div>
                    <div class="col-md-6"><input class="form-control" name="category" placeholder="Category"></div>
                </div>
                <input class="form-control" name="client_name" placeholder="Client name">
                <textarea class="form-control" name="summary" rows="3" placeholder="Project summary"></textarea>
                <textarea class="form-control" name="tech_stack" rows="3" placeholder="Tech stack"></textarea>
                <div class="row g-3">
                    <div class="col-md-6"><input class="form-control" name="sort_order" value="0" placeholder="Sort order"></div>
                    <div class="col-md-6 d-flex align-items-center"><label class="form-check-label"><input class="form-check-input me-2" type="checkbox" name="is_featured" value="1">Featured project</label></div>
                </div>
                <button class="btn btn-primary rounded-pill" type="submit">Add Portfolio Project</button>
            </form>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="dash-card">
            <div class="card-title-row"><h4>Portfolio Projects</h4><span>Frontend showcase items</span></div>
            <?php foreach ($portfolioProjects as $project): ?>
                <form class="dash-form-block" method="post" action="<?= route_url('/admin/portfolio/update') ?>">
                    <input type="hidden" name="project_id" value="<?= e((string) $project['id']) ?>">
                    <div class="row g-3">
                        <div class="col-md-4"><input class="form-control" name="title" value="<?= e($project['title']) ?>" placeholder="Title"></div>
                        <div class="col-md-4"><input class="form-control" name="slug" value="<?= e($project['slug']) ?>" placeholder="Slug"></div>
                        <div class="col-md-4"><input class="form-control" name="category" value="<?= e($project['category']) ?>" placeholder="Category"></div>
                        <div class="col-md-5"><input class="form-control" name="client_name" value="<?= e($project['client_name']) ?>" placeholder="Client name"></div>
                        <div class="col-md-3"><input class="form-control" name="sort_order" value="<?= e((string) $project['sort_order']) ?>" placeholder="Sort order"></div>
                        <div class="col-md-4 d-flex align-items-center"><label class="form-check-label"><input class="form-check-input me-2" type="checkbox" name="is_featured" value="1" <?= (int) ($project['is_featured'] ?? 0) === 1 ? 'checked' : '' ?>>Featured</label></div>
                        <div class="col-12"><textarea class="form-control" name="summary" rows="3" placeholder="Summary"><?= e($project['summary']) ?></textarea></div>
                        <div class="col-12"><textarea class="form-control" name="tech_stack" rows="2" placeholder="Tech stack"><?= e($project['tech_stack']) ?></textarea></div>
                    </div>
                    <div class="admin-tools mt-3">
                        <span class="badge-soft"><?= e($project['slug']) ?></span>
                        <div class="toolbar-actions">
                            <button class="btn btn-primary" type="submit">Save</button>
                            <button class="btn btn-danger" type="submit" formaction="<?= route_url('/admin/portfolio/delete') ?>" onclick="return confirm('Delete this project?')">Delete</button>
                        </div>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </div>
</div>
