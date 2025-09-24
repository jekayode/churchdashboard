<x-sidebar-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Member Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8" x-data="profileEdit()">
            @php
                $user = Auth::user();
                $member = $user->member;
            @endphp

            @if($member)
                <!-- Local nav -->
                <div class="mb-4 flex items-center gap-4">
                    <a href="{{ route('member.profile') }}" class="text-gray-600 hover:text-blue-600">Details</a>
                    <a href="{{ route('member.profile.edit') }}" class="text-blue-600 font-medium">Edit</a>
                    <a href="{{ route('member.profile.security') }}" class="text-gray-600 hover:text-blue-600">Security</a>
                </div>

                <!-- Toast -->
                <div x-show="showToast" class="mb-4 rounded-md p-3" :class="toastType==='success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'">
                    <span x-text="toastMessage"></span>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    @include('member.profile.partials.edit-form', ['member' => $member])
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="text-center">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">No Member Profile Found</h3>
                            <p class="text-gray-600 mb-4">You don't have a member profile associated with your account.</p>
                            <a href="{{ route('member.profile-completion') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Create Member Profile
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        function profileEdit(){
            return {
                showToast:false,toastMessage:'',toastType:'success',
                previewProfile:null, previewCouple:null,
                maritalStatus: '{{ $member->marital_status }}',
                // spouse search state
                spouseQuery:'', spouseResults:[], searching:false, showResults:false, selectedSpouse: {!! $member->spouse_id ? json_encode(['id'=>$member->spouse_id,'name'=>$member->spouse->name ?? 'Spouse']) : 'null' !!},
                onProfileChange(e){ const f=e.target.files[0]; if(f){ this.previewProfile=URL.createObjectURL(f);} },
                onCoupleChange(e){ const f=e.target.files[0]; if(f){ this.previewCouple=URL.createObjectURL(f);} },
                clearSpouse(){ this.selectedSpouse=null; },
                selectSpouse(m){ this.selectedSpouse=m; this.spouseQuery=m.name; this.showResults=false; },
                queueSearchSpouses(){
                    this.showResults=true;
                    if(this._spouseTimer){ clearTimeout(this._spouseTimer); }
                    this._spouseTimer=setTimeout(()=>this.searchSpouses(), 300);
                },
                searchSpouses(){
                    const q=this.spouseQuery.trim(); if(q.length<2){ this.spouseResults=[]; return; }
                    this.searching=true;
                    const params=new URLSearchParams({ q, exclude_id: '{{ $member->id }}', branch_id: '{{ $member->branch_id }}' });
                    fetch('{{ route('member.spouse.search') }}?'+params.toString(),{ headers:{ 'Accept':'application/json' }}).then(r=>r.json()).then(d=>{
                        this.spouseResults=Array.isArray(d) ? d.map(x=>({ id:x.id, name:x.name })) : []; this.searching=false;
                    }).catch(()=>{ this.searching=false; this.spouseResults=[]; });
                },
                submitForm(){
                    const form=this.$refs.editForm;const formData=new FormData(form);
                    if(this.$refs.profileFile && this.$refs.profileFile.files[0]){ formData.append('profile_image', this.$refs.profileFile.files[0]); }
                    if(this.maritalStatus==='married' && this.$refs.coupleFile && this.$refs.coupleFile.files[0]){ formData.append('couple_image', this.$refs.coupleFile.files[0]); }
                    if(this.selectedSpouse && this.maritalStatus==='married'){ formData.set('spouse_id', this.selectedSpouse.id); }
                    fetch('{{ route('member.profile.update') }}',{method:'POST',body:formData,headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content'),'X-Requested-With':'XMLHttpRequest'}})
                    .then(r=>r.json()).then(d=>{this.toastMessage=d.message||'Profile updated.';this.toastType=d.success?'success':'error';this.showToast=true;setTimeout(()=>this.showToast=false,5000);});
                }
            }
        }
    </script>
</x-sidebar-layout>


