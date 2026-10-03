<div id="titlebar" class="gradient">
    <div class="container">
        <div class="row">
            <div class="col-md-12">

                <div class="user-profile-titlebar">
                    <div class="user-profile-avatar"><img src="{{ auth()->user()->image }}"  alt=""></div>
                    <div class="user-profile-name">
                        <h2>{{auth()->user()->name}}</h2>
                        <h4>{{auth()->user()->email}}</h4>
                        <h4>{{auth()->user()->phone}}</h4>
                        <span style="display:inline-block;margin-top:6px;font-size:12px;font-weight:600;padding:3px 12px;border-radius:20px;background:#EE1D48;color:#fff;">{{ auth()->user()->getrole->name }}</span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

