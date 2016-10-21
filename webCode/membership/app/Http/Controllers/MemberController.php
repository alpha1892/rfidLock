<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\Member;
use App\Models\MemberTier;
use App\Models\PaymentProvider;
use App\Models\Resource;

use Session;
use View;

class MemberController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($status = 'active')
    {
        if ($status == 'active')
        {
            $status_id = 1;
        }
        else 
        {
            $status_id = 2;
        }
        $members = Member::where('member_status_id', $status_id)->orderBy('name')->get();
        return view('members.index', compact('members'));
    }

    /**
     * Show the form for creating a new resource.
     *S
     * @return \Illuminate\Http\Response
     */
    public function create(Member $member)
    {
        $tiers = MemberTier::pluck('description', 'id');
        $providers = PaymentProvider::pluck('description', 'id');

        $member->memberTier = MemberTier::where('description', 'Standard')->first();
        $member->paymentProvider = PaymentProvider::where('description', 'Paypal')->first();
        
        return view('members.create', compact('member', 'tiers', 'providers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'email' => 'required|unique:members|max:255',
            'rfid' => 'required|unique:members|max:50'
        ]);

        $input = $request->all();
        
        $member = new Member;
        $member->fill($input);
        
        $member->member_status_id = 1;

        $member->save();

        $member->resources()->attach([1,2]);
        
        // redirect
        Session::flash('message', 'Successfully saved member!');
        
        return redirect('members');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $member = Member::find($id);
        $tiers = MemberTier::pluck('description', 'id');
        $providers = PaymentProvider::pluck('description', 'id');
        $resources = Resource::pluck('description', 'id');

        return view('members.edit', compact('member', 'tiers', 'providers', 'resources'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'email' => 'required|max:255|unique:members,email,'.$id,
            'rfid' => 'required|max:50|unique:members,rfid,'.$id
        ]);

        $input = $request->all();
        
        $member = Member::find($id);
        
        $member->fill($input);
        
        $member->save();

        $member->resources()->sync($request->get('resources'));
        // redirect
        Session::flash('message', 'Successfully saved member!');
        return redirect('members');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $member = Member::find($id);
        $member->member_status_id = 2;
        $member->save();

        return redirect('members');
    }
}