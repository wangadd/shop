<?php

namespace App\Admin\Controllers;

use App\Model\WxMediaModel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Storage;

class WxmediaController extends Controller
{
    use HasResourceActions;
    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());
    }
    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }
    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }
    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WxMediaModel);

        $grid->id('Id');
        $grid->openid('Openid');
        $grid->add_time('Add time');
        $grid->msgtype('Msgtype');
        $grid->msg_id('Msg id');
        $grid->format('Format');
        $grid->mediaid('Mediaid');
        $grid->file_name('File name');
        $grid->file_path('File path')->display(function($path){
                $url = Storage::url($path);
                return "<img src='".$url."'>";
        });

        return $grid;
    }
    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(WxMediaModel::findOrFail($id));

        $show->id('Id');
        $show->openid('Openid');
        $show->add_time('Add time');
        $show->msgtype('Msgtype');
        $show->msg_id('Msg id');
        $show->format('Format');
        $show->mediaid('Mediaid');
        $show->file_name('File name');
        $show->file_path('File path');

        return $show;
    }
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new WxMediaModel);

        $form->text('openid', 'Openid');
        $form->number('add_time', 'Add time');
        $form->text('msgtype', 'Msgtype');
        $form->text('msg_id', 'Msg id');
        $form->text('format', 'Format');
        $form->text('mediaid', 'Mediaid');
        $form->text('file_name', 'File name');
        $form->text('file_path', 'File path');

        return $form;
    }
}
