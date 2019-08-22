import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { ProductRoutingModule } from './product-routing.module';
import { ProductComponent } from './product.component';
import { ProductListComponent} from './product-list/product-list.component';
import { ProductFilterSidebarComponent } from './product-filter-sidebar/product-filter-sidebar.component';
import { RouterModule } from '@angular/router';
import { ProductDetailsComponent } from './product-details/product-details.component';
import { NgxUiLoaderModule } from 'ngx-ui-loader';
import { CartComponent } from './cart/cart.component';

@NgModule({
  declarations: [ProductComponent,ProductListComponent,ProductFilterSidebarComponent, ProductDetailsComponent, CartComponent],
  imports: [
    CommonModule,
    RouterModule,
    ProductRoutingModule,
    NgxUiLoaderModule
  ]
})
export class ProductModule { }
