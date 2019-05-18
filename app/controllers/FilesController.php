<?php

/**
 * This class is handled by a modal whenever needed. No default layout will be used.
 */
class FilesController extends BaseController
{

  public function showModal($id)
  {
    $view = View::make('quotes.files');
    $view->quote = Quote::find($id);
    return $view;
  }
  public function attachToggle($id)
  {
    $file = FrugalFile::find($id);
    $file->attached = $file->attached ? 0 : 1;
    $file->save();
    $not = $file->attached ? "is" : "is not";
    return Response::json(['status' => 'success', 'action' => 'reassign', 'message' => "File {$not} Attached"]);
  }

  public function upload($id)
  {
    $quote = Quote::find($id);

    if (Input::hasFile('frugalFile'))
    {
      $origname = Input::file('frugalFile')->getClientOriginalName();
      $path = "files/$quote->id/";
      Input::file('frugalFile')->move($path, $origname);
      $file = new FrugalFile;
      $file->location = $origname;
      $file->description = Input::get('description');
      $file->user_id = Auth::user()->id;
      $file->quote_id = $quote->id;
      $file->save();
    }
    return Redirect::back();
  }

  public function delete($id)
  {
    $file = FrugalFile::find($id);
    $file->delete();
    if (Request::ajax())
      return Response::json(['status' => 'success', 'action' => 'selfreload']);
    else
      return Redirect::back();
  }


}