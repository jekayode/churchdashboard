<x-sidebar-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Member Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8" x-data="securityPage()">
            <!-- Local nav -->
            <div class="mb-4 flex items-center gap-4">
                <a href="{{ route('member.profile') }}" class="text-gray-600 hover:text-blue-600">Details</a>
                <a href="{{ route('member.profile.edit') }}" class="text-gray-600 hover:text-blue-600">Edit</a>
                <a href="{{ route('member.profile.security') }}" class="text-blue-600 font-medium">Security</a>
            </div>

            <!-- Toast -->
            <div x-show="showToast" class="mb-4 rounded-md p-3" :class="toastType==='success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'">
                <span x-text="toastMessage"></span>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-8">
                <!-- Change Password -->
                <div>
                    <h4 class="font-medium text-gray-900 mb-4">Change Password</h4>
                    <form @submit.prevent="submitPasswordForm" x-ref="passwordForm" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                            <input type="password" name="current_password" id="current_password" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                            <input type="password" name="password" id="password" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Update Password</button>
                        </div>
                    </form>
                </div>

                <!-- Delete Account -->
                <div>
                    <h4 class="font-medium text-red-900 mb-4">Delete Account</h4>
                    <p class="text-red-700 text-sm mb-4">Once you delete your account, there is no going back. Please be certain.</p>
                    <form @submit.prevent="submitDeleteForm" x-ref="deleteForm" class="space-y-4">
                        @csrf
                        @method('DELETE')
                        <div>
                            <label for="delete_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <input type="password" name="password" id="delete_password" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="Enter your password to confirm">
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">Delete Account</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function securityPage(){
            return {
                showToast:false,toastMessage:'',toastType:'success',
                submitPasswordForm(){
                    const form=this.$refs.passwordForm;const formData=new FormData(form);
                    fetch('{{ route('password.update') }}',{method:'POST',body:formData,headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content'),'X-Requested-With':'XMLHttpRequest'}})
                    .then(r=>r.json()).then(d=>{this.toastMessage=d.message||'Password updated successfully.';this.toastType=d.success?'success':'error';this.showToast=true;setTimeout(()=>this.showToast=false,5000);if(d.success){form.reset();}});
                },
                submitDeleteForm(){
                    const form=this.$refs.deleteForm;const formData=new FormData(form);
                    fetch('{{ route('profile.destroy') }}',{method:'POST',body:formData,headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content'),'X-Requested-With':'XMLHttpRequest'}})
                    .then(r=>r.json()).then(d=>{this.toastMessage=d.message||'Account deleted successfully.';this.toastType=d.success?'success':'error';this.showToast=true;if(d.success){setTimeout(()=>window.location.href='/',3000);}else{setTimeout(()=>this.showToast=false,5000);}});
                }
            }
        }
    </script>
</x-sidebar-layout>


