<?php

namespace Modules\Chatbot\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Http\Controllers\Controller;
use Modules\Chatbot\Models\ChatbotSession;

class ChatbotController extends Controller
{
    use ControllerTrait;

    public function __construct(ChatbotSession $model)
    {
        $this->model = $model::getModel();
    }

    protected function getData()
    {
        return $this->model->orderByDesc('last_active_at');
    }

    /**
     * Halaman riwayat percakapan sesi.
     */
    public function getShow($id)
    {
        $model = $this->model->with('has_chat')->findOrFail($id);

        return $this->views('pages.chatbot.show', ['model' => $model]);
    }
}
