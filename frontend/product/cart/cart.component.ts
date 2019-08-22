import { Component, OnInit } from '@angular/core';
import { NgxUiLoaderService } from 'ngx-ui-loader';
import { AppService } from 'src/app/service/app.service';

@Component({
  selector: 'app-cart',
  templateUrl: './cart.component.html',
  styleUrls: ['./cart.component.css']
})
export class CartComponent implements OnInit {

  constructor(private ngxService: NgxUiLoaderService, private appService:AppService) { }

  ngOnInit() {
    this.appService.maintainStatus();
    this.ngxService.start();
    this.ngxService.stop();
  }
}
