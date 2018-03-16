</div>
<?php if(!is_404()) : ?>
    <footer class="container-fluid p-4 p-sm-5 mt-2 bg-haiti">
        <div class="row">
            <div class="col-12 col-lg">
                <ul class="p-4 d-lg-flex justify-content-md-center mb-md-0">
                    <li class="border border-white border-left-0 border-right-0 border-bottom-0">
                        <input type="checkbox" checked>
                        <i></i>
                        <h6 class="text-white font-weight-bold mb-lg-4">Mapa do site <i class="material-icons mt-2 symbol"></i></h2>
                        <?php for($i=0;$i<=4;$i++){ ?>
                            <p class="text-oslo-gray">
                                <i class="d-none d-md-inline-block material-icons text-white fiber">fiber_manual_record</i> Página <?=$i;?>
                            </p>
                        <?php } ?>
                    </li>
                    <li class="border border-white border-left-0 border-right-0 border-bottom-0">
                        <input type="checkbox" checked>
                        <i></i>
                        <h6 class="text-white font-weight-bold mb-lg-4">Funções de administração <i class="material-icons mt-2 symbol"></i></h2>
                        <?php for($i=0;$i<=3;$i++){ ?>
                            <p class="text-oslo-gray">
                                <i class="d-none d-md-inline-block material-icons text-white fiber">fiber_manual_record</i> Administração <?=$i;?>
                            </p>
                        <?php } ?>
                    </li>
                    <li class="border border-white border-left-0 border-right-0 border-bottom-0">
                        <input type="checkbox" checked>
                        <i></i>
                        <h6 class="text-white font-weight-bold mb-lg-4">Funções do usuário <i class="material-icons mt-2 symbol"></i></h2>
                        <?php for($i=0;$i<=2;$i++){ ?>
                            <p class="text-oslo-gray">
                                <i class="d-none d-md-inline-block material-icons text-white fiber">fiber_manual_record</i> Usuário <?=$i;?>
                            </p>
                        <?php } ?>
                    </li>
                    <li class="border border-white border-left-0 border-right-0">
                        <input type="checkbox" checked>
                        <i></i>
                        <h6 class="text-white font-weight-bold mb-lg-4">Redes sociais <i class="material-icons mt-2 symbol"></i></h2>
                        <p class="text-oslo-gray">
                            <svg style="width:1rem;height:1rem" viewBox="0 0 24 24">
                                <path fill="#fff" d="M17,2V2H17V6H15C14.31,6 14,6.81 14,7.5V10H14L17,10V14H14V22H10V14H7V10H10V6A4,4 0 0,1 14,2H17Z" />
                            </svg>
                            Facebook
                        </p>
                        <p class="text-oslo-gray">
                            <svg style="width:1rem;height:1rem" viewBox="0 0 24 24">
                                <path fill="#fff" d="M10,16.5V7.5L16,12M20,4.4C19.4,4.2 15.7,4 12,4C8.3,4 4.6,4.19 4,4.38C2.44,4.9 2,8.4 2,12C2,15.59 2.44,19.1 4,19.61C4.6,19.81 8.3,20 12,20C15.7,20 19.4,19.81 20,19.61C21.56,19.1 22,15.59 22,12C22,8.4 21.56,4.91 20,4.4Z" />
                            </svg>
                            Youtube
                        </p>
                        <p class="text-oslo-gray">
                            <svg style="width:1rem;height:1rem" viewBox="0 0 24 24">
                                <path fill="#fff" d="M22.46,6C21.69,6.35 20.86,6.58 20,6.69C20.88,6.16 21.56,5.32 21.88,4.31C21.05,4.81 20.13,5.16 19.16,5.36C18.37,4.5 17.26,4 16,4C13.65,4 11.73,5.92 11.73,8.29C11.73,8.63 11.77,8.96 11.84,9.27C8.28,9.09 5.11,7.38 3,4.79C2.63,5.42 2.42,6.16 2.42,6.94C2.42,8.43 3.17,9.75 4.33,10.5C3.62,10.5 2.96,10.3 2.38,10C2.38,10 2.38,10 2.38,10.03C2.38,12.11 3.86,13.85 5.82,14.24C5.46,14.34 5.08,14.39 4.69,14.39C4.42,14.39 4.15,14.36 3.89,14.31C4.43,16 6,17.26 7.89,17.29C6.43,18.45 4.58,19.13 2.56,19.13C2.22,19.13 1.88,19.11 1.54,19.07C3.44,20.29 5.7,21 8.12,21C16,21 20.33,14.46 20.33,8.79C20.33,8.6 20.33,8.42 20.32,8.23C21.16,7.63 21.88,6.87 22.46,6Z" />
                            </svg>
                            Twitter
                        </p>
                        <p class="text-oslo-gray">
                            <svg style="width:1rem;height:1rem" viewBox="0 0 24 24">
                                <path fill="#fff" d="M23,11H21V9H19V11H17V13H19V15H21V13H23M8,11V13.4H12C11.8,14.4 10.8,16.4 8,16.4C5.6,16.4 3.7,14.4 3.7,12C3.7,9.6 5.6,7.6 8,7.6C9.4,7.6 10.3,8.2 10.8,8.7L12.7,6.9C11.5,5.7 9.9,5 8,5C4.1,5 1,8.1 1,12C1,15.9 4.1,19 8,19C12,19 14.7,16.2 14.7,12.2C14.7,11.7 14.7,11.4 14.6,11H8Z" />
                            </svg>
                            Google Plus
                        </p>
                    </li>
                </ul>
            </div>
        </div>
        <hr class="bg-scooter"/>
        <div class="row p-4">
            <div class="col text-white font-weight-normal">
                <p>
                    Instituição
                </p>
                <p>
                    Endereço
                </p>
                <p>
                    Email
                        <br>
                    Telefone
                </p>
            </div>
            <div class="col-auto pr-0 pr-md-3 d-none d-sm-block align-self-md-center">
                <img src="<?php echo get_template_directory_uri() ?>/assets/images/logo-footer.svg" alt="">
            </div>
        </div>
    </footer>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>