<?php

namespace App\Core;

class View
{
    public static function render(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        $payload = apply_filters('view.render', [
            'view' => $view,
            'layout' => $layout,
            'data' => $data,
        ]);
        $payload = is_array($payload) ? $payload : [];

        $view = (string) ($payload['view'] ?? $view);
        $layout = (string) ($payload['layout'] ?? $layout);
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : $data;

        extract($data);
        $viewFile = view_path($view . '.php');
        $layoutFile = view_path($layout . '.php');

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $pagePayload = apply_filters('view.output', [
            'view' => $view,
            'layout' => $layout,
            'data' => $data,
            'content' => $content,
            'view_file' => $viewFile,
            'layout_file' => $layoutFile,
        ]);
        $pagePayload = is_array($pagePayload) ? $pagePayload : [];

        $content = (string) ($pagePayload['content'] ?? $content);
        do_action('onPageRender', $pagePayload);

        require $layoutFile;
    }
}
